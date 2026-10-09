# Historique des modifications du projet

Ce fichier trace toutes les modifications apportées au projet afin de pouvoir les reproduire facilement sur une nouvelle installation.

## 1. Installation de la base de données sans mot de passe
**Objectif** : Rendre le mot de passe de la base de données facultatif lors de l'installation.
- **Fichier** : \esources/views/installation/step3.blade.php\`n  - **Modification** : Suppression des attributs \equired\ et \min="8"\ sur l'input \DB_PASSWORD\.
- **Fichier** : \pp/Traits/InstallationTrail.php\ (méthode \updateEnvironmentFile\)
  - **Modification** : Ajout de \?? ''\ pour gérer correctement les mots de passe vides (\\['DB_PASSWORD'] ?? ''\).
- **Fichier** : \pp/Http/Controllers/InstallController.php\ (méthode \databaseInstallation\)
  - **Modification** : Ajout de \?? ''\ lors de l'appel à \checkDatabaseConnection\ (\\['DB_PASSWORD'] ?? ''\).

## 2. Correction du plantage lors de l'installation (Erreur 500)
**Objectif** : Empêcher le script d'installation et de mise à jour de planter à cause d'une méthode manquante.
- **Fichier** : \pp/Traits/InstallationTrail.php\`n  - **Modification** : Ajout de la méthode manquante vide \public function cleanupEnvFileContext(): void {}\ à la fin du fichier.

## 3. Désactivation et masquage du Captcha sur la page de connexion Admin
**Objectif** : Retirer le captcha de la page de login de l'administration pour simplifier la connexion.
- **Fichier** : \pp/Http/Controllers/Admin/Auth/LoginController.php\ (méthode \login\)
  - **Modification** : Mise en commentaire de la vérification backend du captcha (\RecaptchaService::verificationStatus\).
- **Fichier** : \esources/views/admin-views/auth/login.blade.php\`n  - **Modification** : Mise en commentaire (avec \{{-- ... --}}\) de l'intégralité du bloc \@if(isset(\)...)\ générant le captcha, pour le masquer visuellement et empêcher les scripts JS de bloquer le bouton "Sign in".

## 4. Forcer l'utilisation de APP_URL pour les redirections
**Objectif** : Corriger le bug où Laravel redirige vers \localhost\ après le login au lieu du vrai domaine, souvent causé par une configuration serveur proxy/Apache/Nginx.
- **Fichier** : \pp/Providers/AppServiceProvider.php\ (méthode \oot\)
  - **Modification** : Ajout de \\URL::forceRootUrl(config('app.url'));\ pour forcer le framework à utiliser la variable \APP_URL\ pour générer toutes ses routes et redirections.

## 5. Correction du sous-dossier (URL localhost/admin) pour le login
**Objectif** : Empêcher Laravel de supprimer le sous-dossier \/admin\ lors de la génération des actions de formulaire, ce qui causait la redirection vers \localhost/login\ lors du clic sur Sign in.
- **Fichier** : \.env\`n  - **Modification** : Suppression des valeurs de \APP_HOST_DOMAIN\ et \APP_HOST_BASE_DOMAIN\ (elles étaient définies sur \localhost\ par l'installateur, ce qui forçait la génération des URL à la racine \localhost\ sans tenir compte du sous-dossier de \APP_URL\).

## 6. Planification : Suppression des références à "6valley" (En attente d'exécution)
**Objectif** : Retirer toutes les mentions, bannières et logos de l'ancien nom "6valley" dans le code, la base de données et les ressources visuelles.
- **Code source et Vues** : Suppression des textes "Buy genuine 6valley", des bannières CodeCanyon, et modification du fichier \.env\ (\APP_NAME\, \CONTAINER_NAME_PREFIX\).
- **Base de données** : Remplacement dynamique du nom de l'entreprise (\company_name\) et des images associées via un script ou l'interface d'administration.
- **Ressources / Assets** : Identification et mise à jour des logos codés en dur (\public/assets/...\) afin qu'ils pointent vers les fichiers personnalisés.

## 7. Dépannage du Registre Client (API) et Migration de Base de Données
**Objectif** : Corriger l'erreur 500 (Internal Server Error) lors de l'inscription côté client en raison du champ NNI.
- **Base de données** : Exécution de la commande php artisan migrate --force pour créer les tables manquantes (
ni_records, price_reduction_requests, eduction_tiers, et l'ajout des colonnes 
ni et phone sur la table users).
- **Application Flutter** : Ajout du champ NNI dans le widget sign_up_widget.dart et dans RegisterModel.dart, pour que le NNI soit correctement envoyé vers /api/v1/auth/register.
- **Application Flutter (UI)** : Correction des débordements (RenderFlex overflow) sur home_explore_screen.dart et sur custom_textfield_widget.dart (le sélecteur de code pays de téléphone).
