<?php
session_start();

function setSessionUser($user)
{
    $_SESSION['user'] = $user;
}

function logoutSession()
{
    $_SESSION['user'] = false;
}
