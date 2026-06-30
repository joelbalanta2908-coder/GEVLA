-- =====================================================================
--  DATOS DEMO / CONSISTENCIA DEL PROYECTO  (sena_disciplinario)
--  Script idempotente: puede ejecutarse varias veces sin duplicar.
--
--  Corrige un hueco de datos: no existia el rol "Aprendiz" ni las
--  asignaciones de rol para los usuarios aprendices, por lo que estos
--  no podian ingresar a su portal. Aqui se crean.
-- =====================================================================

-- Rol Aprendiz (la tabla rol tiene clave unica en nombre_rol)
INSERT IGNORE INTO `rol` (`id_rol`, `nombre_rol`) VALUES
(4, 'Aprendiz');

-- Asignacion del rol Aprendiz a los usuarios que son aprendices
-- (usuario 3 = Juan Diaz, 4 = Ana Torres, 5 = Luis Martinez)
INSERT IGNORE INTO `usuario_rol` (`id_usuario_rol`, `id_usuario`, `id_rol`, `fecha_asignacion`, `estado_asignacion`) VALUES
(4, 3, 4, '2023-03-01 08:00:00', 'activa'),
(5, 4, 4, '2023-03-01 08:00:00', 'activa'),
(6, 5, 4, '2023-08-07 08:00:00', 'activa');
