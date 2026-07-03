-- ============================================================================
--  Módulo de Notificaciones por Usuario (campanita del panel)
--  Script para importar en phpMyAdmin / MariaDB sobre la base `sena_disciplinario`.
--
--  Equivalente a la migración de Laravel:
--    2026_07_03_000000_create_notificacion_usuario_table.php
--
--  Crea la tabla `notificacion_usuario`: cada usuario (de cualquier rol) tiene
--  sus propias notificaciones y su estado de lectura se guarda en la base de
--  datos, por lo que persiste al cerrar sesión y volver a entrar.
--
--  Es seguro ejecutarlo varias veces (usa IF NOT EXISTS e INSERT IGNORE).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `notificacion_usuario` (
  `id_notificacion_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `mensaje` VARCHAR(500) DEFAULT NULL,
  `url` VARCHAR(255) DEFAULT NULL,
  `leida` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_creacion` DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion_usuario`),
  KEY `idx_notif_usuario_leida` (`id_usuario`, `leida`),
  CONSTRAINT `fk_notif_usuario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Notificaciones de demostración coherentes con los datos demo:
--  · Coordinadores (usuarios 1 y 8): nuevos llamados pendientes de revisión.
--  · Instructores: actualizaciones del estado de sus llamados.
--  · Aprendices: llamados de atención que les fueron registrados.
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `notificacion_usuario` (`id_notificacion_usuario`, `id_usuario`, `titulo`, `mensaje`, `url`, `leida`, `fecha_creacion`) VALUES
-- Coordinador Carlos Pérez (usuario 1)
(1, 1, 'Nuevo llamado de atención', 'Paola Vargas registró un llamado para Isabella Correa: No entrega de evidencias del trimestre', '/coordinacion/llamados/13', 0, '2026-06-20 10:05:00'),
(2, 1, 'Nuevo llamado de atención', 'Sandra Ruiz registró un llamado para Sebastián Rojas: Bajo rendimiento en evaluaciones', '/coordinacion/llamados/6', 0, '2026-06-02 09:15:00'),
-- Coordinador Misional Lucía Ramírez (usuario 8)
(3, 8, 'Nuevo llamado de atención', 'Paola Vargas registró un llamado para Isabella Correa: No entrega de evidencias del trimestre', '/coordinacion/llamados/13', 0, '2026-06-20 10:05:00'),
-- Instructora María López (usuaria 2): estados de sus llamados
(4, 2, 'Tu llamado cambió de estado', 'El llamado #1 (Inasistencias reiteradas) pasó a estado Notificado', '/instructor/llamados/1', 1, '2026-03-18 11:00:00'),
(5, 2, 'Tu llamado cambió de estado', 'El llamado #2 (Comportamiento inadecuado) pasó a estado En revisión', '/instructor/llamados/2', 0, '2026-04-12 09:30:00'),
-- Instructora Paola Vargas (usuaria 18)
(6, 18, 'Tu llamado cambió de estado', 'El llamado #11 (Agresión verbal a un compañero) pasó a estado Notificado', '/instructor/llamados/11', 0, '2026-06-01 08:40:00'),
-- Aprendiz Juan Díaz (usuario 3)
(7, 3, 'Nuevo llamado de atención', 'Se te registró un llamado de atención: Inasistencias reiteradas', '/aprendiz/llamados/1', 0, '2026-03-15 10:00:00'),
-- Aprendiz Isabella Correa (usuaria 23)
(8, 23, 'Nuevo llamado de atención', 'Se te registró un llamado de atención: No entrega de evidencias del trimestre', '/aprendiz/llamados/13', 0, '2026-06-20 10:05:00'),
-- Aprendiz Laura Giraldo (usuaria 19)
(9, 19, 'Se expidió un acta de coordinación', 'Acta AC-2026-006: acondicionamiento académico con plan de mejoramiento de 60 días', '/aprendiz/actas/6', 0, '2026-06-05 12:00:00');
