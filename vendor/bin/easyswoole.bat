@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../easyswoole/easyswoole/bin/easyswoole
php "%BIN_TARGET%" %*
