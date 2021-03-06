<?php
#########################################################
# Deutsches Sprachpaket (Formell)                       #
# Version 1.6.8                                         #
# Datum: 27.05.2012                                     #
# MyBB-Version 1.6.8                                    #
# Autor: MyBBoard.de | Webseite: http://www.mybboard.de #
# (c) 2005-2012 MyBBoard.de | Alle Rechte vorbehalten!  #
#                                                       #
# Die Lizenz/Nutzungsbedingungen finden Sie in der      #
# beiliegenden Readme.                                  #
#########################################################

$l['admin_permissions'] = "Administrator-Berechtigungen";
$l['user_permissions'] = "Benutzer-Berechtigungen";
$l['user_permissions_desc'] = "Hier können Sie die Administrator-Berechtigungen für die einzelnen Benutzer verwalten. Dies erlaubt Ihnen, verschiedenen Administratoren den Zugriff auf bestimmte Bereiche zu verwehren.";
$l['group_permissions'] = "Benutzergruppen-Berechtigungen";
$l['group_permissions_desc'] = "Administrator-Berechtigungen können auch Benutzergruppen zugeordnet werden, die Zugriff auf das Admin-CP haben. Genauso können Sie ganzen Gruppen den Zugriff auf bestimmte Bereiche verwehren.";
$l['default_permissions'] = "Standard-Berechtigungen";
$l['default_permissions_desc'] = "Die Standard-Berechtigungen werden für Benutzer verwendet, für die keine eigenen Administrator-Berechtigungen gesetzt wurden, oder für Benutzer in Gruppen ohne Benutzergruppen-Berechtigungen.";

$l['admin_permissions_updated'] = "Die Administrator-Berechtigungen wurden erfolgreich aktualisiert.";
$l['revoke_permissions'] = "Berechtigungen zurücknehmen";
$l['edit_permissions'] = "Berechtigungen ändern";
$l['set_permissions'] = "Berechtigungen setzen";
$l['edit_permissions_desc'] = "Hier können Sie den Zugriff auf ganze Tabs oder einzelne Seiten beschränken. Beachten Sie, dass alle Administratoren den Tab \"Startseite\" betreten können.";
$l['update_permissions'] = "Berechtigungen aktualisieren";
$l['view_log'] = "Log-Daten ansehen";
$l['permissions_type_group'] = "Berechtigungs-Typ der Gruppe";
$l['permissions_type_user'] = "Berechtigungs-Typ des Benutzers";
$l['no_group_perms'] = "Es sind keine Benutzer-Berechtigungen definiert.";
$l['no_user_perms'] = "Es sind keine Benutzergruppen-Berechtigungen definiert.";
$l['edit_user'] = "Benutzer-Profil bearbeiten";
$l['using_individual_perms'] = "Benutzt individuelle Berechtigung";
$l['using_custom_perms'] = "Benutzt eigene Berechtigung";
$l['using_group_perms'] = "Benutzt Benutzergruppen-Berechtigung";
$l['using_default_perms'] = "Benutzt Standard-Berechtigung";
$l['last_active'] = "Zuletzt aktiv";
$l['user'] = "Benutzer";
$l['edit_group'] = "Gruppe bearbeiten";
$l['default'] = "Standard";
$l['group'] = "Gruppe";

$l['error_delete_super_admin'] = 'Der ausgewählte Benutzer ist ein Super-Administrator. Daher können Sie diese Funktion nicht verwenden.<br /><br />Um dies trotzdem zu ermöglichen muss Ihre Benutzer-ID in die Liste der Super-Administratoren in der inc/config.php eingefügt werden.';
$l['error_delete_no_uid'] = 'Sie haben keine Benutzer-/Gruppen-Berechtigungs-ID eingegeben';
$l['error_delete_invalid_uid'] = 'Die eingegebene Benutzer-/Gruppen-Berechtigungs-ID ist ungültig';

$l['success_perms_deleted'] = 'Die Administrator-Berechtigung wurde erfolgreich zurückgenommen.';

$l['confirm_perms_deletion'] = "Wollen Sie die Administrator-Berechtigung wirklich zurücknehmen?";
$l['confirm_perms_deletion2'] = "Wollen Sie die Benutzer-Berechtigung wirklich zurücknehmen?";

?>
