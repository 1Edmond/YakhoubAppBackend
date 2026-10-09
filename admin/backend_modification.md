# Modifications Backend MultiShop Tchad

Ce document résume l'ensemble des ajouts et modifications effectués sur l'API backend Laravel (`C:\laragon\www\admin`) pour répondre aux besoins de la plateforme MultiShop Tchad.

---

## 1. Négociation de Prix par Produit dans le Panier (CDC 3.4.7)
- **Base de données & Migration :**
  - Migration : `database/migrations/2026_09_27_000001_add_cart_id_to_price_reduction_requests_table.php`.
  - Ajout des colonnes `cart_id` et `product_id` (nullable) dans la table `price_reduction_requests`.
  - `order_id` rendu nullable pour permettre les négociations initiées directement depuis le panier.
- **Modèle `PriceReductionRequest` (`app/Models/PriceReductionRequest.php`) :**
  - Ajout de `cart_id` et `product_id` dans `$fillable`.
  - Relations Eloquent ajoutées : `cart(): BelongsTo`, `product(): BelongsTo`.
- **Service Métier `PriceReductionService` (`app/Services/PriceReductionService.php`) :**
  - Méthode `createCartRequest(Cart $cart, User $customer, $requestedReduction)` :
    - Vérification qu'aucune négociation n'est déjà en cours sur cette ligne.
    - Création de la demande avec proposition de prix calculée et premier round.
  - `acceptRequest()` :
    - Si la demande concerne un panier (`cart_id`), applique automatiquement le montant négocié en réduction directe sur le panier client (`$cart->discount = requested_reduction`).
  - `respondToCounterOffer()` :
    - Si le client accepte la contre-proposition vendeur, applique immédiatement le montant proposé au panier (`$cart->discount = counter_offer_amount`).
- **Événements & Notifications :**
  - `ReductionRequestedEvent` adapté avec constructeur `?Order $order = null` pour fonctionner hors commande.
- **Contrôleur Client (`app/Http/Controllers/RestAPI/v1/PriceReductionController.php`) :**
  - `createCartRequest(Request $request)` : route `POST /api/v1/customer/cart/negotiate`.
  - `getCartNegotiations(Request $request)` : route `GET /api/v1/customer/cart/negotiations`.
  - `respondToCounterOffer(Request $request, $requestId)` : route `PUT /api/v1/customer/cart/negotiations/{requestId}/respond`.
- **Contrôleur Panier (`app/Http/Controllers/RestAPI/v1/CartController.php`) :**
  - Dans `getCartList`, injection dynamique de l'état de négociation pour chaque article (`negotiation_status`, `negotiation_reduction`, `counter_offer_amount`, `negotiation_id`, `negotiation_round`, `negotiated_price`).
- **Contrôleur Vendeur (`app/Http/Controllers/RestAPI/v3/seller/ReductionController.php`) :**
  - Dans `getRequests`, eager-loading de `customer`, `product`, `order` pour afficher les demandes de panier comme de commande.

---

## 2. Authentification & Numéro National d'Identification (NNI)
- **Vendeurs (Sellers) :**
  - Validation du champ `nni` (maximum 20 caractères, unique dans `nni_records`) dans `app/Http/Controllers/RestAPI/v2/seller/auth/RegisterController.php` et `v3`.
  - Enregistrement du NNI dans la table `nni_records`.
- **Clients (Customers) :** 
  - `CustomerAPIAuthController.php` (v1) : validation et insertion du NNI dans `nni_records` et la table `users`.

---

## 3. Commande (Checkout - Adresses Tchad et Coordonnées)
- **Controller modifié :** `app/Http/Controllers/RestAPI/v1/OrderController.php`.
- **Méthode utilitaire `updateTchadFields` :**
  - Récupère et met à jour les champs `delivery_quarter`, `delivery_street`, `delivery_description`, `door_latitude`, `door_longitude`.
  - Gère l'upload de l'image de la porte via `door_photo` (sauvegardée via `ImageManager`) et insère le lien dans `door_photo_url`.
- **Méthodes impactées :** Intégration de cet utilitaire à la fin de `place_order`, `placeOrderByOfflinePayment`, et `placeOrderByWallet` avant le retour JSON de l'API.

---

## 4. Annulation de Commande et Frais Wallet (1 000 FCFA)
- **Controller modifié :** `app/Http/Controllers/RestAPI/v1/OrderController.php` (méthode `order_cancel`).
- **Logique métier d'annulation :**
  - Un utilisateur ne peut pas annuler une commande déjà `delivered`, `canceled`, `returned` ou `failed`.
  - Si le statut de la commande n'est PAS `pending` (ex. `confirmed`, `processing`), l'annulation déduit automatiquement **1000 FCFA** du portefeuille (wallet) du client.
  - Vérification du solde du wallet préalable. S'il est inférieur à 1000 FCFA, l'annulation est bloquée.
  - Utilisation de `CustomerManager::create_wallet_transaction` pour tracer cette déduction via le type `order_cancellation`.
- **CustomerManager :** `app/Utils/CustomerManager.php` prend en charge le type `order_cancellation` comme un débit sur le wallet.

---

## 5. Paiement Mobile (Airtel & Moov Money)
- **Controller modifié :** `app/Http/Controllers/RestAPI/v1/MobilePaymentController.php`.
- **Logique ajoutée :**
  - Pour les paiements via Airtel (`initiateAirtel`) et Moov (`initiateMoov`), le paramètre `payment_phone` est obligatoire (`required`).
  - Le numéro utilisé pour le paiement est tracé via `\Log::info()` pour l'audit et le débogage.

---

## 6. Installation & Configuration Système
- Rendre le mot de passe de base de données facultatif lors de l'installation locale (`step3.blade.php`, `InstallationTrail.php`, `InstallController.php`).
- Correction de l'erreur 500 (`InstallationTrail.php::cleanupEnvFileContext`).
- Désactivation du captcha sur la page de connexion Admin (`LoginController.php`, `login.blade.php`).
- Forçage de `APP_URL` (`AppServiceProvider.php`).
- Configuration de l'environnement `.env` pour la gestion des sous-dossiers `/admin`.
