<?php
session_start();

// Destruir toda la sesión
session_unset();
session_destroy();

// Redirigir al login o index
header("Location: ../login.html");
exit();