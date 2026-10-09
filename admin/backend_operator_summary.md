# Résumé de la Mission: Opérateurs de Transfert & Frais

1. **Migrations & Modèles Créés :**
   - `TransferOperator` et `transfer_operators` table.
   - `TransferOperatorFee` et `transfer_operator_fees` table.
   
2. **Modifications des Modèles/Migrations Existants :**
   - Ajout de `operator_id` (foreign key) à `transfer_accounts`.
   - Ajout de `transfer_fee_amount` à `order_transfer_proofs`.
   - Relations Laravel définies (`operator()`, `fees()`).

3. **API Admin :**
   - Création de `Admin\TransferOperatorController`.
   - Endpoints définis dans `routes/admin/routes.php` :
     - `GET /transfer-operators`
     - `POST /transfer-operators`
     - `POST /transfer-operators/{operator}/fees`

4. **API Vendeur (Seller API v3) :**
   - Création d'un endpoint `GET /api/v3/seller/transfer-operators` dans `routes/rest_api/v3/seller.php`.
   - Validation de `operator_id` ajoutée dans `SellerTransferAccountController@store` et `@update`.

5. **API Client (Customer API v1) :**
   - `TransferAccountController@getAvailable` modifié pour calculer et retourner `transfer_fee` basé sur les paliers de l'opérateur (fixe ou pourcentage) du montant de la requête.
   
*Note concernant `OrderController@place_order`*: Le système existant peut être patché manuellement en ajoutant le `transfer_fee` reçu ou calculé au montant total de la commande et en le sauvegardant dans `order_transfer_proofs`.
