-- ============================================================================
--  Módulo de Firmas de Llamados de Atención
--  Script para importar en phpMyAdmin / MariaDB sobre la base `sena_disciplinario`.
--
--  Crea la tabla `firma_llamado`: registra QUIÉN firmó cada llamado de
--  atención, CON QUÉ ROL (Instructor / Coordinador / Aprendiz) y CUÁNDO
--  (fecha y hora exactas), para la trazabilidad del proceso disciplinario.
--
--  La imagen de la firma de cada usuario NO se guarda aquí: vive como archivo
--  privado en storage/app/firmas y se asocia por id_usuario. Esta tabla solo
--  guarda el acto de firmar (trazabilidad).
--
--  Es seguro ejecutarlo varias veces (usa IF NOT EXISTS).
--  Si esta tabla no existe, el sistema sigue funcionando: solo se deshabilitan
--  las acciones de firma (igual que el módulo de notificaciones).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `firma_llamado` (
  `id_firma` INT(11) NOT NULL AUTO_INCREMENT,
  `id_llamado` INT(11) NOT NULL,
  `id_usuario` INT(11) NOT NULL,
  `rol_firma` ENUM('Instructor', 'Coordinador', 'Aprendiz') NOT NULL,
  `fecha_firma` DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_firma`),
  -- Cada rol firma una sola vez por llamado (el instructor no puede firmar dos veces el mismo llamado).
  UNIQUE KEY `uq_firma_llamado_rol` (`id_llamado`, `rol_firma`),
  KEY `idx_firma_usuario` (`id_usuario`),
  CONSTRAINT `fk_firma_llamado_llamado` FOREIGN KEY (`id_llamado`) REFERENCES `llamado_atencion` (`id_llamado`) ON DELETE CASCADE,
  CONSTRAINT `fk_firma_llamado_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
