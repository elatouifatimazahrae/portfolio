git add .<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Mon site</title>
    <style>
        :root {
            --olive: #68743f;
            --olive-dark: #4f592f;
            --gold: #c9a227;
            --gold-light: #f4e7ad;
            --cream: #faf9f3;
            --text: #333;
            --muted: #777;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: var(--text);
            background:
                    linear-gradient(
                            135deg,
                            rgba(250, 249, 243, 0.96),
                            rgba(244, 231, 173, 0.5)
                    );
        }

        /* Navbar */
        .navbar {
            width: 100%;
            min-height: 70px;
            background-color: white !important;
            border-bottom: 3px solid var(--gold);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand {
            color: var(--olive-dark) !important;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .navbar .nav-link {
            margin: 0 5px;
            color: var(--olive-dark) !important;
            font-weight: 600;
            border-radius: 7px;
            transition: 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: white !important;
            background-color: var(--olive);
        }

        /* Formulaire */
        .form-container {
            width: 92%;
            max-width: 520px;
            margin: 60px auto;
            padding: 38px;
            background-color: white;
            border-left: 6px solid var(--gold);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, 0.14);
        }

        .form-title {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 28px;
            font-weight: bold;
            text-align: center;
        }

        .form-subtitle {
            margin-bottom: 30px;
            color: var(--muted);
            text-align: center;
            font-size: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 15px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            min-height: 48px;
            margin-bottom: 20px;
            padding: 12px 14px;
            color: var(--text);
            background-color: #fff;
            border: 1px solid #d8d8c8;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--olive);
            box-shadow: 0 0 0 4px rgba(104, 116, 63, 0.15);
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .buton {
            width: 100%;
            min-height: 48px;
            margin-top: 8px;
            padding: 12px;
            color: white;
            background: linear-gradient(
                    135deg,
                    var(--olive),
                    var(--olive-dark)
            );
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .buton:hover {
            background: linear-gradient(
                    135deg,
                    var(--gold),
                    #a98418
            );
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(201, 162, 39, 0.3);
        }

        .alert {
            margin-bottom: 22px;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-success {
            color: #3d5b25;
            background-color: #edf5df;
            border-color: #c9dbab;
        }

        .alert-warning {
            color: #725c12;
            background-color: #fff8dc;
            border-color: #ead58a;
        }

        .alert-danger {
            color: #842029;
            background-color: #fce8e8;
            border-color: #efb8bd;
        }

        @media (max-width: 576px) {
            .form-container {
                width: 90%;
                margin: 35px auto;
                padding: 28px 22px;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>

</head>
<body>

   <?php include "include/nav.php"?>


   <div class="form-container">

   <?php
       require_once "include/data.php";

       $message = '';
       $messageType = '';

       if (isset($_POST['ajouter'])) {
           $login = trim($_POST['login'] ?? '');
           $password = $_POST['password'] ?? '';

           if (empty($login) || empty($password)) {
               $message = "Login et password sont obligatoires.";
               $messageType = "danger";
           } else {
               // Vérifier si le login existe déjà
               $check = $pdo->prepare(
                       "SELECT id FROM utilisateur WHERE login = ?"
               );
               $check->execute([$login]);

               if ($check->fetch()) {
                   $message = "Ce login existe déjà. Choisissez un autre login.";
                   $messageType = "warning";
               } else {
                   // Chiffrer le mot de passe
                   $passwordHash = password_hash(
                           $password,
                           PASSWORD_DEFAULT
                   );

                   $date = date('Y-m-d');

                   $sqlstate = $pdo->prepare(
                           "INSERT INTO utilisateur
                (login, password, date_creation)
                VALUES (?, ?, ?)"
                   );

                   $sqlstate->execute([
                           $login,
                           $passwordHash,
                           $date
                   ]);

                   $message = "Utilisateur ajouté avec succès.";
                   $messageType = "success";
               }
           }
       }
       ?>

       <?php if (!empty($message)): ?>
           <div class="alert alert-<?= htmlspecialchars($messageType) ?>" role="alert">
               <?= htmlspecialchars($message) ?>
           </div>
       <?php endif; ?>

       <form method="post">
           <h1 class="form-title">Ajouter un utilisateur</h1>
           <p class="form-subtitle">
               Créez un nouveau compte utilisateur
           </p>
           <label class="form-label" for="login">Login</label>
           <input
                   type="text"
                   id="login"
                   class="form-control"
                   name="login"
                   placeholder="Entrez le login"
                   required
           >

           <label class="form-label" for="password">Mot de passe</label>
           <input
                   type="password"
                   id="password"
                   class="form-control"
                   name="password"
                   placeholder="Entrez le mot de passe"
                   required
           >

           <button
                   type="submit"
                   class="buton"
                   name="ajouter"
           >
               Ajouter l'utilisateur
           </button>

       </form>
   </div>

</body>
</html>
