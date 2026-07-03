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

-- =====================================================================
--  REGISTROS ADICIONALES DE DEMOSTRACION
--  Mas usuarios, instructores, aprendices, fichas, matriculas, llamados,
--  faltas, procesos, actas y notificaciones para poblar el sistema.
--  Los hashes de contrasena son de ejemplo (contienen "..."), por lo que
--  cada usuario puede iniciar sesion con su numero de documento.
-- =====================================================================

-- ---------------------------------------------------------------------
-- Usuarios nuevos: 2 instructores (9-10) y 6 aprendices (11-16)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `usuario` (`id_usuario`, `numero_documento`, `tipo_documento`, `nombres`, `apellidos`, `correo`, `telefono`, `username`, `password_hash`, `estado_usuario`, `ultimo_acceso`, `fecha_creacion`) VALUES
(9,  '667788990', 'CC', 'Sandra',    'Ruiz',     'sruiz@sena.edu.co',            '3151122334', 'sruiz',     '$2b$10$demo...', 'activo', '2026-06-28 08:15:00', '2024-02-05 09:00:00'),
(10, '778899001', 'CC', 'Jorge',     'Castaño',  'jcastano@sena.edu.co',         '3162233445', 'jcastano',  '$2b$10$demo...', 'activo', '2026-06-30 10:40:00', '2024-07-15 09:00:00'),
(11, '889900112', 'TI', 'Camila',    'Herrera',  'camila.herrera@correo.com',    '3173344556', 'cherrera',  '$2b$10$demo...', 'activo', '2026-06-25 14:20:00', '2023-03-06 08:00:00'),
(12, '990011223', 'CC', 'Andrés',    'Mora',     'andres.mora@correo.com',       '3184455667', 'amora',     '$2b$10$demo...', 'activo', '2026-06-29 09:05:00', '2024-01-15 08:00:00'),
(13, '101112131', 'TI', 'Valentina', 'Quintero', 'valentina.quintero@correo.com','3195566778', 'vquintero', '$2b$10$demo...', 'activo', '2026-07-01 16:30:00', '2024-01-15 08:00:00'),
(14, '121314151', 'CC', 'Sebastián', 'Rojas',    'sebastian.rojas@correo.com',   '3206677889', 'srojas',    '$2b$10$demo...', 'activo', '2026-06-27 11:00:00', '2025-02-03 08:00:00'),
(15, '131415161', 'CC', 'Daniela',   'Osorio',   'daniela.osorio@correo.com',    '3217788990', 'dosorio',   '$2b$10$demo...', 'activo', NULL,                  '2025-02-03 08:00:00'),
(16, '141516171', 'TI', 'Mateo',     'Cárdenas', 'mateo.cardenas@correo.com',    '3228899001', 'mcardenas', '$2b$10$demo...', 'activo', '2026-07-02 07:45:00', '2026-04-01 08:00:00');

-- Roles de los usuarios nuevos (2 = Instructor, 4 = Aprendiz)
INSERT IGNORE INTO `usuario_rol` (`id_usuario_rol`, `id_usuario`, `id_rol`, `fecha_asignacion`, `estado_asignacion`) VALUES
(7,  9,  2, '2024-02-05 09:00:00', 'activa'),
(8,  10, 2, '2024-07-15 09:00:00', 'activa'),
(9,  11, 4, '2023-03-06 08:00:00', 'activa'),
(10, 12, 4, '2024-01-15 08:00:00', 'activa'),
(11, 13, 4, '2024-01-15 08:00:00', 'activa'),
(12, 14, 4, '2025-02-03 08:00:00', 'activa'),
(13, 15, 4, '2025-02-03 08:00:00', 'activa'),
(14, 16, 4, '2026-04-01 08:00:00', 'activa');

-- Perfiles de instructor
INSERT IGNORE INTO `instructor` (`id_instructor`, `id_usuario`, `codigo_instructor`, `area_formacion`, `estado_instructor`) VALUES
(4, 9,  'INS-004', 'Diseño y Multimedia',     'activo'),
(5, 10, 'INS-005', 'Gestión Administrativa',  'activo');

-- Perfiles de aprendiz
INSERT IGNORE INTO `aprendiz` (`id_aprendiz`, `id_usuario`, `correo_institucional`, `correo_personal`, `estado_academico`, `tiene_apoyo_sostenimiento`) VALUES
(4, 11, 'cherrera@aprendiz.sena.edu.co',  'camila.herrera@correo.com',     'en_formacion', 1),
(5, 12, 'amora@aprendiz.sena.edu.co',     'andres.mora@correo.com',        'en_formacion', 0),
(6, 13, 'vquintero@aprendiz.sena.edu.co', 'valentina.quintero@correo.com', 'en_formacion', 1),
(7, 14, 'srojas@aprendiz.sena.edu.co',    'sebastian.rojas@correo.com',    'en_formacion', 0),
(8, 15, 'dosorio@aprendiz.sena.edu.co',   'daniela.osorio@correo.com',     'en_formacion', 0),
(9, 16, 'mcardenas@aprendiz.sena.edu.co', 'mateo.cardenas@correo.com',     'en_formacion', 1);

-- ---------------------------------------------------------------------
-- Programa y fichas nuevas
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `programa_formacion` (`id_programa`, `codigo_programa`, `nombre_programa`, `nivel`, `duracion_meses`) VALUES
(4, '134101', 'Diseño e Integración de Multimedia', 'tecnologo', 24);

INSERT IGNORE INTO `ficha` (`id_ficha`, `id_programa`, `id_instructor_lider`, `numero_ficha`, `modalidad`, `estado_ficha`, `fecha_inicio`, `fecha_fin_programada`) VALUES
(4, 3, 2, '3125678', 'presencial', 'en_ejecucion', '2025-02-03', '2027-02-03'),
(5, 4, 4, '3298765', 'virtual',    'en_ejecucion', '2026-04-01', '2027-10-01');

-- Matriculas de los aprendices nuevos
INSERT IGNORE INTO `matricula` (`id_matricula`, `id_aprendiz`, `id_ficha`, `fecha_matricula`, `estado_matricula`, `es_vocero`, `tipo_vocero`) VALUES
(4,  4, 1, '2023-03-06', 'activa', 0, 'no_es_vocero'),
(5,  5, 3, '2024-01-15', 'activa', 1, 'principal'),
(6,  6, 3, '2024-01-15', 'activa', 0, 'no_es_vocero'),
(7,  7, 4, '2025-02-03', 'activa', 0, 'no_es_vocero'),
(8,  8, 4, '2025-02-03', 'activa', 0, 'no_es_vocero'),
(9,  9, 5, '2026-04-01', 'activa', 0, 'no_es_vocero');

-- Asociacion instructores-fichas (la clave unica id_ficha+id_instructor
-- hace idempotente este bloque aunque no se indique el id del pivote)
INSERT IGNORE INTO `ficha_instructor` (`id_ficha`, `id_instructor`, `fecha_asignacion`) VALUES
(1, 1, '2023-03-06'),
(2, 1, '2023-08-07'),
(3, 2, '2024-01-15'),
(4, 2, '2025-02-03'),
(4, 5, '2025-02-10'),
(5, 4, '2026-04-01'),
(1, 4, '2024-02-10'),
(3, 5, '2024-08-01');

-- ---------------------------------------------------------------------
-- Llamados de atencion nuevos (varios estados para las graficas)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `llamado_atencion` (`id_llamado`, `id_aprendiz`, `id_instructor`, `id_coordinacion`, `id_usuario_reporta`, `fecha_llamado`, `tipo_llamado`, `categoria`, `asunto`, `descripcion_hechos`, `pruebas_aportadas`, `estado_llamado`, `observaciones`) VALUES
(4, 4, 1, NULL, 2,  '2026-05-12', 'llamado_escrito',  'academico',      'Inasistencia a formación',            'La aprendiz faltó 3 días seguidos sin justificación',            'Registro de asistencia de mayo',   'registrado',  NULL),
(5, 5, 2, 1,    2,  '2026-05-20', 'llamado_escrito',  'disciplinario',  'Irrespeto a compañeros',              'Uso de lenguaje inapropiado en el ambiente de formación',        'Testimonios de compañeros',        'en_revision', 'Citado a descargos'),
(6, 7, 4, NULL, 9,  '2026-06-02', 'llamado_escrito',  'academico',      'Bajo rendimiento en evaluaciones',    'No alcanzó los resultados de aprendizaje del trimestre',         'Reporte de calificaciones',        'registrado',  NULL),
(7, 8, 5, 2,    10, '2026-06-10', 'acondicionamiento','disciplinario',  'Incumplimiento del reglamento',       'Incumplió los compromisos de convivencia del ambiente',          'Informe del instructor',           'notificado',  'Notificado al aprendiz'),
(8, 9, 4, 1,    9,  '2026-06-18', 'llamado_escrito',  'academico',      'Entrega incompleta de evidencias',    'No presentó las evidencias del proyecto formativo en plataforma','Registro de la plataforma',        'cerrado',     'Cerrado con compromiso de mejora');

-- Faltas asociadas a los llamados nuevos con proceso
INSERT IGNORE INTO `falta` (`id_falta`, `id_llamado`, `id_aprendiz`, `id_instructor`, `tipo_falta`, `descripcion_hechos`, `fecha_ocurrencia`, `principio_valor_infringido`, `calificacion_falta`, `estado_falta`) VALUES
(4, 5, 5, 2, 'disciplinaria', 'Lenguaje inapropiado hacia compañeros en el ambiente de formación', '2026-05-20', 'Respeto y convivencia',        'grave', 'en_proceso'),
(5, 7, 8, 5, 'disciplinaria', 'Incumplimiento reiterado de los compromisos de convivencia',        '2026-06-10', 'Disciplina y responsabilidad', 'grave', 'en_proceso');

-- Procesos disciplinarios nuevos
INSERT IGNORE INTO `proceso_disciplinario` (`id_proceso`, `id_aprendiz`, `id_llamado`, `etapa_actual`, `fecha_inicio`, `fecha_cierre`, `estado_proceso`, `observaciones`) VALUES
(4, 5, 5, 'llamado_escrito',   '2026-05-25', NULL, 'activo', 'Proceso en primera etapa, a la espera de descargos'),
(5, 8, 7, 'acondicionamiento', '2026-06-12', NULL, 'activo', 'Acondicionamiento disciplinario en curso');

-- Actas de coordinacion nuevas
INSERT IGNORE INTO `acta_coordinacion` (`id_acta`, `id_aprendiz`, `id_falta`, `id_proceso`, `tipo_acta`, `numero_acta`, `fecha_expedicion`, `fecha_notificacion_personal`, `fecha_firmeza`, `sancion_descripcion`, `meses_inhabilitacion`, `estado_acta`) VALUES
(4, 5, 4, 4, 'acondicionamiento_disciplinario', 'AC-2026-004', '2026-06-01', '2026-06-03', NULL, 'Acondicionamiento disciplinario por irrespeto a compañeros', NULL, 'notificado'),
(5, 8, 5, 5, 'acondicionamiento_disciplinario', 'AC-2026-005', '2026-06-15', NULL,         NULL, 'Plan de acondicionamiento por incumplimiento del reglamento', NULL, 'expedido');

-- Notificaciones nuevas (alimentan la campanita y los portales)
INSERT IGNORE INTO `notificacion` (`id_notificacion`, `id_aprendiz`, `id_acta`, `id_falta`, `id_llamado`, `tipo_notificacion`, `fecha_envio`, `medio_envio`, `contenido_resumen`, `estado_notificacion`) VALUES
(4, 4, NULL, NULL, 4, 'comunicado_llamado',           '2026-05-13', 'correo_institucional', 'Se registró un llamado de atención por inasistencia a formación',      'enviada'),
(5, 5, NULL, NULL, 5, 'citacion',                     '2026-05-21', 'correo_institucional', 'Citación a descargos por falta disciplinaria del 20 de mayo',          'enviada'),
(6, 5, 4,    NULL, NULL, 'aviso_acta',                '2026-06-02', 'correo_personal',      'Acta AC-2026-004 expedida y notificada al aprendiz',                   'enviada'),
(7, 8, NULL, NULL, 7, 'comunicado_acondicionamiento', '2026-06-11', 'correo_institucional', 'Notificación de acondicionamiento disciplinario en curso',             'recibida'),
(8, 9, NULL, NULL, 8, 'comunicado_llamado',           '2026-06-19', 'correo_institucional', 'Llamado de atención cerrado con compromiso de mejora académica',       'recibida');
