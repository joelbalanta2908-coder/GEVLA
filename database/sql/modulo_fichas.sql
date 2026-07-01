-- ============================================================================
--  Módulo de Gestión de Fichas e Instructores
--  Script para importar en phpMyAdmin / MariaDB sobre la base `sena_disciplinario`.
--
--  Equivalente a las migraciones de Laravel:
--    2026_07_01_000000_create_ficha_instructor_table.php
--    2026_07_01_000001_add_fecha_asignacion_lider_to_ficha.php
--    2026_07_01_000002_create_historial_instructor_lider_table.php
--
--  Es seguro ejecutarlo aunque los objetos ya existan (usa IF NOT EXISTS y un
--  procedimiento para agregar la columna solo si falta).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1) Tabla pivote ficha_instructor (varios instructores por ficha)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ficha_instructor` (
  `id_ficha_instructor` INT(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` INT(11) NOT NULL,
  `id_instructor` INT(11) NOT NULL,
  `fecha_asignacion` DATE DEFAULT NULL,
  PRIMARY KEY (`id_ficha_instructor`),
  UNIQUE KEY `uq_ficha_instructor` (`id_ficha`, `id_instructor`),
  KEY `fk_ficha_instructor_instructor` (`id_instructor`),
  CONSTRAINT `fk_ficha_instructor_ficha` FOREIGN KEY (`id_ficha`) REFERENCES `ficha` (`id_ficha`) ON DELETE CASCADE,
  CONSTRAINT `fk_ficha_instructor_instructor` FOREIGN KEY (`id_instructor`) REFERENCES `instructor` (`id_instructor`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------------
-- 2) Columna fecha_asignacion_lider en la tabla ficha (agregada solo si falta)
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `gevla_add_fecha_asignacion_lider`;
DELIMITER //
CREATE PROCEDURE `gevla_add_fecha_asignacion_lider`()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ficha'
      AND COLUMN_NAME = 'fecha_asignacion_lider'
  ) THEN
    ALTER TABLE `ficha`
      ADD COLUMN `fecha_asignacion_lider` DATE DEFAULT NULL AFTER `id_instructor_lider`;
  END IF;
END //
DELIMITER ;
CALL `gevla_add_fecha_asignacion_lider`();
DROP PROCEDURE IF EXISTS `gevla_add_fecha_asignacion_lider`;

-- ---------------------------------------------------------------------------
-- 3) Auditoría de cambios de instructor líder
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `historial_instructor_lider` (
  `id_historial_lider` INT(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` INT(11) NOT NULL,
  `id_instructor_anterior` INT(11) DEFAULT NULL,
  `id_instructor_nuevo` INT(11) NOT NULL,
  `id_usuario_registra` INT(11) NOT NULL,
  `fecha_cambio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historial_lider`),
  KEY `fk_hist_lider_ficha` (`id_ficha`),
  KEY `fk_hist_lider_anterior` (`id_instructor_anterior`),
  KEY `fk_hist_lider_nuevo` (`id_instructor_nuevo`),
  KEY `fk_hist_lider_usuario` (`id_usuario_registra`),
  CONSTRAINT `fk_hist_lider_ficha` FOREIGN KEY (`id_ficha`) REFERENCES `ficha` (`id_ficha`) ON DELETE CASCADE,
  CONSTRAINT `fk_hist_lider_anterior` FOREIGN KEY (`id_instructor_anterior`) REFERENCES `instructor` (`id_instructor`) ON DELETE SET NULL,
  CONSTRAINT `fk_hist_lider_nuevo` FOREIGN KEY (`id_instructor_nuevo`) REFERENCES `instructor` (`id_instructor`),
  CONSTRAINT `fk_hist_lider_usuario` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------------
-- 4) Backfill: cada instructor líder actual queda también asociado a su ficha
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO `ficha_instructor` (`id_ficha`, `id_instructor`, `fecha_asignacion`)
SELECT `id_ficha`, `id_instructor_lider`, `fecha_inicio`
FROM `ficha`
WHERE `id_instructor_lider` IS NOT NULL;

-- ---------------------------------------------------------------------------
-- 5) Columna tipo_docente en instructor (materia / transversal) — módulo Docentes
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `gevla_add_tipo_docente`;
DELIMITER //
CREATE PROCEDURE `gevla_add_tipo_docente`()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'instructor'
      AND COLUMN_NAME = 'tipo_docente'
  ) THEN
    ALTER TABLE `instructor`
      ADD COLUMN `tipo_docente` ENUM('materia','transversal') DEFAULT NULL AFTER `area_formacion`;
  END IF;
END //
DELIMITER ;
CALL `gevla_add_tipo_docente`();
DROP PROCEDURE IF EXISTS `gevla_add_tipo_docente`;
