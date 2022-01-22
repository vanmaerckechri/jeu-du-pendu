<?php
    function choisir_motAuHasard(): string
    {
        $dictionnaire = file('assets/docs/dictionnaire.txt', FILE_IGNORE_NEW_LINES); // charge le fichier et place chaque ligne dans un tableau.
        $i = rand(0, count($dictionnaire) - 1); // Choisir un nombre au hasard en fonction du nombre de valeurs dans le dictionnaire (de ce cas-ci de 0 à 2).
        return strtoupper($dictionnaire[$i]); // Retourner le mot sélectionné (strtoupper converti les lettres du mot en majuscules).
    }
    function lancer_nouvellePartie(): void
    {
        $_SESSION['vies'] = 6;
        $_SESSION['motMystere'] = choisir_motAuHasard();
        $_SESSION['lettresProposées'] = [];
        $_SESSION['statutFinDePartie'] = null;
    }
    function verifier_lettre(string $lettre): void
    {
        // Si la lettre proposée par le joueur n'a pas encore été proposée:
        if (array_search($lettre, $_SESSION['lettresProposées']) === false)
        {
            // Si la lettre proposée n'est pas présente dans le mot mystère:
            if (strpos($_SESSION['motMystere'], $lettre) === false)
            {
                $_SESSION['vies'] -= 1; // Retirer une vie.
            }
            array_push($_SESSION['lettresProposées'], $lettre); // Ajouter la lettre à la liste de lettres déjà proposées.
        }
    }
    function constituer_mot(): void
    {
        $_SESSION['lettresDecouvertes'] = ""; // reinitialiser le mot en construction.
        // Parcourir chaque lettre du mot mystère:
        for ($i = 0, $j = strlen($_SESSION['motMystere']); $i < $j; $i++)
        {
            // Si la lettre actuelle du mot mystère est présente dans les lettres proposées par le joueur:
            if (array_search($_SESSION['motMystere'][$i], $_SESSION['lettresProposées']) !== false)
            {
                $_SESSION['lettresDecouvertes'] .= $_SESSION['motMystere'][$i] . " "; // Ajouter la lettre suivie d'un espace au mot en construction.
            }
            else
            {
                // Si le statut de la partie est une défaite:
                if ($_SESSION['vies'] === 0)
                {
                    $_SESSION['lettresDecouvertes'] .= "<b class=\"defaite\">" . $_SESSION['motMystere'][$i] . "</b> "; // Ajouter la lettre non trouvée en rouge.
                }
                else
                {
                    $_SESSION['lettresDecouvertes'] .= "_ "; // Ajouter la charactère signalant que la lettre n'a pas encore été trouvée.
                }
            }
        }
    }
    function actualiser_statutFinDePartie(): void
    {
        // Si le nombre de vie a atteint 0:
        if ($_SESSION['vies'] === 0)
        {
            $_SESSION['statutFinDePartie'] = "<b class=\"defaite\">defaite!</b>";
        }
        // Si toutes les lettres ont été trouvées:
        elseif (strpos($_SESSION['lettresDecouvertes'], "_") === false)
        {
            $_SESSION['statutFinDePartie'] = "<b class=\"victoire\">victoire!</b>";
        }
    }
    function gerer_jeuDuPendu(): void
    {
        session_start();
        // Si le joueur lance une nouvelle partie (soit en chargeant la page pour la première fois soit en ayant appuyé sur le bouton de nouvelle partie):
        if (empty($_POST) || isset($_POST['nouvellePartie']))
        {
            lancer_nouvellePartie();
        }
        // Le joueur propose une lettre et la partie est tjs en cours:
        elseif ($_SESSION['statutFinDePartie'] == null)
        {
            verifier_lettre($_POST['lettre']);
        }
        // Constituer le mot avec les lettres validées:
        constituer_mot();
        // Actualiser la situation de la partie (partie est terminée?):
        actualiser_statutFinDePartie();
    }
    gerer_jeuDuPendu();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Le jeu du pendu réalisé lors du cours d'approche en développement donné par Bruno Martin durant la formation de webdesigner à l'ifosup de Wavre.">
    <meta name="author" content="Christophe Van Maercke">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FAVICON: fichiers et code généré avec https://realfavicongenerator.net/ -->
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="assets/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <!-- FIN FAVICON -->
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Le Jeu du Pendu</title>
</head>
<body>
    <main id="jeuDuPendu">
        <h1>Jeu du Pendu</h1>
        <div class="lettres-container">
            <div class="lettresDecouvertes"><?=$_SESSION['lettresDecouvertes']?></div>
            <form method="POST">
                <fieldset>
                    <?php
                    /* Parcourir les lettres de l'alphabet: Si la lettre a déjà été proposée ou si la partie est terminée, désactiver cette dernière. */
                    foreach(range("A", "Z") as $lettre)
                    {
                    ?>
                        <input type="submit" name="lettre" value="<?=$lettre?>" <?=array_search($lettre, $_SESSION['lettresProposées']) !== false || $_SESSION['statutFinDePartie'] != null ? "disabled" : "" ?>>
                    <?php 
                    }
                    ?>
                    <div><?=$_SESSION['statutFinDePartie'] ?? ""?></div>
                </fieldset>
                <input type="submit" name="nouvellePartie"  value="nouvelle partie">
            </form>
        </div>
        <div class="vies-container">
            <img src="assets/images/le-jeu-du-pendu-etape-<?=6 - $_SESSION['vies']?>.png" alt="" width="240" height="240">
            <?php
                if ($_SESSION['vies'] > 0)
                {
                ?>
                    <div class="enVie"><?=$_SESSION['vies']?></div>
                <?php
                }
                else
                {
                ?>
                    <div class="defaite"></div>
                <?php
                }
            ?>
        </div>
        <a href="https://github.com/vanmaerckechri/jeu-du-pendu/archive/refs/heads/main.zip" target="_blank" rel="noopener">télécharger les fichiers</a>
    </main>
</body>
</html>