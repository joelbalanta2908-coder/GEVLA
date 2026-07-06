-- ============================================================================
--  MODULO LOGIN - GEVLA
--  Columna remember_token en la tabla usuario: es donde Laravel guarda la
--  llave del "Recordarme" del inicio de sesion. Sin esta columna, marcar la
--  casilla "Recordarme" produce un error al iniciar sesion.
--
--  Es seguro reejecutarlo (usa IF NOT EXISTS). Importar en sena_disciplinario.
--  Equivale a la migracion 2026_07_06_000000_add_remember_token_to_usuario.
-- ============================================================================

ALTER TABLE `usuario`
  ADD COLUMN IF NOT EXISTS `remember_token` VARCHAR(100) NULL DEFAULT NULL AFTER `password_hash`;
