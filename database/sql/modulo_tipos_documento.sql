-- ============================================================================
--  Ampliación del catálogo de tipos de documento del usuario
--  Script para importar en phpMyAdmin / MariaDB sobre la base `sena_disciplinario`.
--
--  Agrega al ENUM `usuario.tipo_documento` los valores:
--    · PPT (Permiso por Protección Temporal)
--    · PA  (Pasaporte)
--
--  Los valores existentes (CC, TI, CE, PEP) se conservan tal cual, por lo que
--  los registros actuales no se ven afectados. Es seguro ejecutarlo varias
--  veces (MODIFY con la misma definición es idempotente).
-- ============================================================================

ALTER TABLE `usuario`
  MODIFY `tipo_documento` ENUM('CC', 'TI', 'CE', 'PEP', 'PPT', 'PA') NOT NULL;
