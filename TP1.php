<?php
/*
Tout le code doit se faire dans ce fichier PHP

Réalisez un formulaire HTML contenant :
- firstname
- lastname
- email
- pwd
- pwdConfirm

Créer une table "user" dans la base de données, regardez le .env à la racine et faites un build de docker
si vous n'arrivez pas à les récupérer pour qu'il les prenne en compte

Lors de la validation du formulaire vous devez :
- Nettoyer les valeurs, exemple trim sur l'email et lowercase (5 points)
- Attention au mot de passe (3 points)
- Attention à l'unicité de l'email (4 points)
- Vérifier les champs sachant que le prénom et le nom sont facultatifs
- Insérer en BDD avec PDO et des requêtes préparées si tout est OK (4 points)
- Sinon afficher les erreurs et remettre les valeurs pertinantes dans les inputs (4 points)

Le design je m'en fiche mais pas la sécurité

Bonus de 3 points si vous arrivez à envoyer un mail via un compte SMTP de votre choix
pour valider l'adresse email en bdd

Pour le : 22 Octobre 2025 - 8h
M'envoyer un lien par mail de votre repo sur y.skrzypczyk@gmail.com
Objet du mail : TP1 - 2IW3 - Nom Prénom
Si vous ne savez pas mettre votre code sur un repo envoyez moi une archive
*/

$host = 'db';
$port = '5432';
$dbname = 'devdb';
$user = 'devuser';
$pass = 'devpass';


try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {}

?>


<form action="TP1.php" method="post">
   
<div>
<label>Votre nom :</label>
   <input name="nom" id="nom" type="text" />

   <label>Votre prénom :</label>
   <input name="prenom" id="prenom" type="text" /></p>

   <label>Votre email :</label>
   <input name="email" id="email" type="text" />
<div>
<div>
   <div>
      <label>Entrez un mot de passe :</label>
      <input name="mdp" id="mdp" type="password" /></p>
      <p>1 maj, 1 min, 1 symbole, 8-20 caractères<p>
   </div>
   <div>
      <label>Confirmer votre mot de passe :</label>
      <input name="mdpconfirm" id="mdpconfirm" type="password" />
   </div>   
<div>
   <button type="submit">Valider</button>

<?php

function checkAndHashMDP($mdp, $mdpconfirm){
   $mdp_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/'; 
   if($mdp == $mdpconfirm){
      if(preg_match($mdp_regex, $mdp)) {
      $mdp_hashed = password_hash($mdp, PASSWORD_DEFAULT);
      return $mdp_hashed;
   } else {
      echo "bad password";
      if (strlen($mdp) < 8 || strlen($mdp) > 20) {
         echo "<br/>le mdp doit faire entre 8 et 20 caractères<br/>";
      }
      if(!preg_match('/[a-z]/', $mdp)){
         echo "au moins une minuscule<br/>";
      }
      if(!preg_match('/[A-Z]/', $mdp)){
         echo "au moins une majuscule<br/>";
      }
      if(!preg_match('/[0-9]/', $mdp)){
         echo "au moins un chiffre<br/>";
      }
      if(!preg_match('/[@$!%*?&]/', $mdp)){
         echo "au moins un caractère spécial<br/>";
         }
      }
   } else echo "<br/>Les mots de passe sont différents!!!";
   
}


function parseAndCheckEmail($email){
   $email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
   $email = trim($email);
   $email = strtolower($email);
   if(preg_match($email_regex, $email)) {
      return $email;
   } else {echo "Veuillez renseigner une adresse email valide";} 
}


if($_SERVER['REQUEST_METHOD'] == "POST") {
   $donnees = array();
   
   if($_POST['nom']){
      $nom = $_POST['nom'];
      $donnees["nom"] = $nom;
   }
   if($_POST['prenom']){
      $prenom = $_POST['prenom'];
      $donnees["prenom"]=$prenom;
   }
   if(!$_POST['email']){
      echo "</br>Veuillez renseigner un email</br>";
   } else {
      $email = parseAndCheckEmail($_POST['email']);
      if (strlen($email)>1){$donnees["email"] = $email;}
   }
   if(!$_POST['mdp']){
      echo "</br>Veuillez renseigner un mot de passe</br>";
   } else {
      if(!$_POST['mdpconfirm']){
         echo "</br>Veuillez confirmer votre mot de passe</br>";
      } else {
         $mdp_hasher = checkAndHashMDP($_POST["mdp"], $_POST["mdpconfirm"]);
         if ($mdp_hasher AND strlen($mdp_hasher)>1){$donnees["mdp"] = $mdp_hasher;}
      }
   }


   if($donnees["email"] && $donnees["mdp"]){
      $sql = "SELECT * FROM users WHERE email = :email";
      $req = $pdo->prepare($sql);
      $req->execute(['email' => $donnees["email"]]);

      $rep = $req->fetch(PDO::FETCH_ASSOC);
      if ($rep) {
         echo "email déjà utilisé";
         if($donnees["nom"]){
            echo "<script>document.getElementById('nom').value ='".$donnees["nom"]."'</script>";
         }
         if($donnees["prenom"]){
            echo "<script>document.getElementById('prenom').value ='".$donnees["prenom"]."'</script>";
         }
         echo "<script>document.getElementById('mdp').value ='".$_POST["mdp"]."'</script>";
         echo "<script>document.getElementById('mdpconfirm').value ='".$_POST["mdpconfirm"]."'</script>";
      } else {
         $sql = "INSERT INTO users (lastname, firstname, password, email) VALUES (:nom, :prenom, :mdp, :email)";
         $req = $pdo->prepare($sql);
         $req->execute([
            "nom" => $donnees["nom"] ?: null,
            "prenom" => $donnees["prenom"] ?: null,
            "mdp" => $donnees["mdp"],
            "email" => $donnees["email"]
         ]);
      }
   }
   

}











