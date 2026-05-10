<?php
// Einstiegspunkt für /training/. Leitet auf das Dashboard weiter.
// Eingeloggte Mitglieder landen direkt dort; nicht-eingeloggte werden
// von dashboard.php's requireLogin() automatisch zur Login-Seite geschickt.
header('Location: /training/dashboard.php');
exit;
