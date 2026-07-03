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

-- =====================================================================
--  SEGUNDO LOTE DE DATOS DEMO
--  Más instructores, aprendices, programas y fichas, junto con cuatro
--  procesos disciplinarios/académicos completos y lógicos:
--    A) Académico: 2 llamados por inasistencia -> acondicionamiento (activo)
--    B) Disciplinario: agresión verbal -> acondicionamiento en firme
--    C) Disciplinario muy grave: suplantación -> cancelación de matrícula
--    D) Académico reciente: llamado registrado pendiente de revisión
-- =====================================================================

-- Usuarios nuevos: 2 instructores (17-18) y 6 aprendices (19-24)
INSERT IGNORE INTO `usuario` (`id_usuario`, `numero_documento`, `tipo_documento`, `nombres`, `apellidos`, `correo`, `telefono`, `username`, `password_hash`, `estado_usuario`, `ultimo_acceso`, `fecha_creacion`) VALUES
(17, '151617181', 'CC', 'Ricardo',  'Peña',     'rpena@sena.edu.co',             '3230011223', 'rpena',     '$2b$10$demo...', 'activo', '2026-07-01 08:00:00', '2025-01-20 09:00:00'),
(18, '161718192', 'CC', 'Paola',    'Vargas',   'pvargas@sena.edu.co',           '3241122334', 'pvargas',   '$2b$10$demo...', 'activo', '2026-07-02 09:30:00', '2025-06-02 09:00:00'),
(19, '171819202', 'TI', 'Laura',    'Giraldo',  'laura.giraldo@correo.com',      '3252233445', 'lgiraldo',  '$2b$10$demo...', 'activo', '2026-06-30 15:10:00', '2025-07-14 08:00:00'),
(20, '181920213', 'CC', 'Kevin',    'Palacios', 'kevin.palacios@correo.com',     '3263344556', 'kpalacios', '$2b$10$demo...', 'activo', '2026-06-26 10:20:00', '2025-07-14 08:00:00'),
(21, '192021224', 'CC', 'Sara',     'Mejía',    'sara.mejia@correo.com',         '3274455667', 'smejia',    '$2b$10$demo...', 'activo', '2026-05-30 09:00:00', '2025-07-14 08:00:00'),
(22, '202122235', 'TI', 'Nicolás',  'Zapata',   'nicolas.zapata@correo.com',     '3285566778', 'nzapata',   '$2b$10$demo...', 'activo', '2026-07-01 18:40:00', '2026-01-19 08:00:00'),
(23, '212223246', 'CC', 'Isabella', 'Correa',   'isabella.correa@correo.com',    '3296677889', 'icorrea',   '$2b$10$demo...', 'activo', '2026-06-24 12:05:00', '2026-01-19 08:00:00'),
(24, '222324257', 'CC', 'Tomás',    'Agudelo',  'tomas.agudelo@correo.com',      '3307788990', 'tagudelo',  '$2b$10$demo...', 'activo', NULL,                  '2026-01-19 08:00:00');

-- Roles (2 = Instructor, 4 = Aprendiz)
INSERT IGNORE INTO `usuario_rol` (`id_usuario_rol`, `id_usuario`, `id_rol`, `fecha_asignacion`, `estado_asignacion`) VALUES
(15, 17, 2, '2025-01-20 09:00:00', 'activa'),
(16, 18, 2, '2025-06-02 09:00:00', 'activa'),
(17, 19, 4, '2025-07-14 08:00:00', 'activa'),
(18, 20, 4, '2025-07-14 08:00:00', 'activa'),
(19, 21, 4, '2025-07-14 08:00:00', 'activa'),
(20, 22, 4, '2026-01-19 08:00:00', 'activa'),
(21, 23, 4, '2026-01-19 08:00:00', 'activa'),
(22, 24, 4, '2026-01-19 08:00:00', 'activa');

-- Perfiles de instructor
INSERT IGNORE INTO `instructor` (`id_instructor`, `id_usuario`, `codigo_instructor`, `area_formacion`, `estado_instructor`) VALUES
(6, 17, 'INS-006', 'Mercadeo y Ventas',           'activo'),
(7, 18, 'INS-007', 'Redes y Telecomunicaciones',  'activo');

-- Perfiles de aprendiz (Sara Mejía queda cancelada: historia C)
INSERT IGNORE INTO `aprendiz` (`id_aprendiz`, `id_usuario`, `correo_institucional`, `correo_personal`, `estado_academico`, `tiene_apoyo_sostenimiento`) VALUES
(10, 19, 'lgiraldo@aprendiz.sena.edu.co',  'laura.giraldo@correo.com',   'en_formacion', 1),
(11, 20, 'kpalacios@aprendiz.sena.edu.co', 'kevin.palacios@correo.com',  'en_formacion', 0),
(12, 21, 'smejia@aprendiz.sena.edu.co',    'sara.mejia@correo.com',      'cancelado',    0),
(13, 22, 'nzapata@aprendiz.sena.edu.co',   'nicolas.zapata@correo.com',  'en_formacion', 1),
(14, 23, 'icorrea@aprendiz.sena.edu.co',   'isabella.correa@correo.com', 'en_formacion', 0),
(15, 24, 'tagudelo@aprendiz.sena.edu.co',  'tomas.agudelo@correo.com',   'en_formacion', 1);

-- Programas y fichas nuevos
INSERT IGNORE INTO `programa_formacion` (`id_programa`, `codigo_programa`, `nombre_programa`, `nivel`, `duracion_meses`) VALUES
(5, '621201', 'Gestión de Mercados',                   'tecnologo', 24),
(6, '228118', 'Mantenimiento de Equipos de Cómputo',   'tecnico',   18);

INSERT IGNORE INTO `ficha` (`id_ficha`, `id_programa`, `id_instructor_lider`, `numero_ficha`, `modalidad`, `estado_ficha`, `fecha_inicio`, `fecha_fin_programada`) VALUES
(6, 5, 6, '3356789', 'presencial', 'en_ejecucion', '2025-07-14', '2027-07-14'),
(7, 6, 7, '3401234', 'presencial', 'en_ejecucion', '2026-01-19', '2027-07-19');

-- Matrículas (la de Sara Mejía queda cancelada por la historia C)
INSERT IGNORE INTO `matricula` (`id_matricula`, `id_aprendiz`, `id_ficha`, `fecha_matricula`, `estado_matricula`, `es_vocero`, `tipo_vocero`) VALUES
(10, 10, 6, '2025-07-14', 'activa',    1, 'principal'),
(11, 11, 6, '2025-07-14', 'activa',    0, 'no_es_vocero'),
(12, 12, 6, '2025-07-14', 'cancelada', 0, 'no_es_vocero'),
(13, 13, 7, '2026-01-19', 'activa',    0, 'no_es_vocero'),
(14, 14, 7, '2026-01-19', 'activa',    1, 'suplente'),
(15, 15, 7, '2026-01-19', 'activa',    0, 'no_es_vocero');

-- Asociación instructores-fichas
INSERT IGNORE INTO `ficha_instructor` (`id_ficha`, `id_instructor`, `fecha_asignacion`) VALUES
(6, 6, '2025-07-14'),
(6, 7, '2025-07-20'),
(7, 7, '2026-01-19'),
(7, 6, '2026-01-25');

-- ---------------------------------------------------------------------
-- Historia A (académica, activa): Laura Giraldo (aprendiz 10, ficha 6)
-- Dos llamados por inasistencia -> proceso con acondicionamiento académico
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `llamado_atencion` (`id_llamado`, `id_aprendiz`, `id_instructor`, `id_coordinacion`, `id_usuario_reporta`, `fecha_llamado`, `tipo_llamado`, `categoria`, `asunto`, `descripcion_hechos`, `pruebas_aportadas`, `estado_llamado`, `observaciones`) VALUES
(9,  10, 6, 1,    17, '2026-04-20', 'llamado_escrito', 'academico', 'Inasistencias reiteradas en abril',   'La aprendiz acumuló 4 inasistencias sin justificación en abril',            'Registro de asistencia de abril',   'cerrado',    'Primer llamado; se acordó compromiso de asistencia'),
(10, 10, 6, 1,    17, '2026-05-18', 'llamado_escrito', 'academico', 'Reincidencia en inasistencias',       'La aprendiz reincidió con 3 nuevas inasistencias tras el primer llamado',   'Registro de asistencia de mayo',    'notificado', 'Segundo llamado en la categoría (Art. 46): abre proceso');

INSERT IGNORE INTO `falta` (`id_falta`, `id_llamado`, `id_aprendiz`, `id_instructor`, `tipo_falta`, `descripcion_hechos`, `fecha_ocurrencia`, `principio_valor_infringido`, `calificacion_falta`, `estado_falta`) VALUES
(6, 10, 10, 6, 'academica', 'Reincidencia en inasistencias tras compromiso previo', '2026-05-18', 'Responsabilidad y compromiso formativo', 'grave', 'en_proceso');

INSERT IGNORE INTO `proceso_disciplinario` (`id_proceso`, `id_aprendiz`, `id_llamado`, `etapa_actual`, `fecha_inicio`, `fecha_cierre`, `estado_proceso`, `observaciones`) VALUES
(6, 10, 10, 'acondicionamiento', '2026-05-22', NULL, 'activo', 'Acondicionamiento académico con plan de mejoramiento de 60 días');

INSERT IGNORE INTO `historial_proceso_disciplinario` (`id_historial`, `id_proceso`, `etapa`, `fecha_registro`, `id_usuario_registra`, `descripcion`, `resultado`) VALUES
(4, 6, 'llamado_escrito',   '2026-05-22 09:00:00', 1, 'Apertura del proceso por segundo llamado académico (Art. 46)', 'Se cita a la aprendiz a descargos'),
(5, 6, 'acondicionamiento', '2026-06-05 10:30:00', 1, 'Descargos presentados; se determina acondicionamiento académico', 'Plan de mejoramiento por 60 días');

INSERT IGNORE INTO `acta_coordinacion` (`id_acta`, `id_aprendiz`, `id_falta`, `id_proceso`, `tipo_acta`, `numero_acta`, `fecha_expedicion`, `fecha_notificacion_personal`, `fecha_firmeza`, `sancion_descripcion`, `meses_inhabilitacion`, `estado_acta`) VALUES
(6, 10, 6, 6, 'acondicionamiento_academico', 'AC-2026-006', '2026-06-05', '2026-06-06', NULL, 'Acondicionamiento académico con plan de mejoramiento de 60 días', NULL, 'notificado');

-- ---------------------------------------------------------------------
-- Historia B (disciplinaria, en firme): Nicolás Zapata (aprendiz 13, ficha 7)
-- Agresión verbal -> acondicionamiento disciplinario en firme
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `llamado_atencion` (`id_llamado`, `id_aprendiz`, `id_instructor`, `id_coordinacion`, `id_usuario_reporta`, `fecha_llamado`, `tipo_llamado`, `categoria`, `asunto`, `descripcion_hechos`, `pruebas_aportadas`, `estado_llamado`, `observaciones`) VALUES
(11, 13, 7, 2, 18, '2026-05-28', 'llamado_escrito', 'disciplinario', 'Agresión verbal a un compañero', 'El aprendiz agredió verbalmente a un compañero durante la formación', 'Testimonios de tres compañeros y del instructor', 'notificado', 'Falta grave; se abre proceso disciplinario');

INSERT IGNORE INTO `falta` (`id_falta`, `id_llamado`, `id_aprendiz`, `id_instructor`, `tipo_falta`, `descripcion_hechos`, `fecha_ocurrencia`, `principio_valor_infringido`, `calificacion_falta`, `estado_falta`) VALUES
(7, 11, 13, 7, 'disciplinaria', 'Agresión verbal a un compañero en el ambiente de formación', '2026-05-28', 'Respeto y convivencia', 'grave', 'resuelto');

INSERT IGNORE INTO `proceso_disciplinario` (`id_proceso`, `id_aprendiz`, `id_llamado`, `etapa_actual`, `fecha_inicio`, `fecha_cierre`, `estado_proceso`, `observaciones`) VALUES
(7, 13, 11, 'acondicionamiento', '2026-06-01', NULL, 'activo', 'Acondicionamiento disciplinario con compromiso de convivencia firmado');

INSERT IGNORE INTO `historial_proceso_disciplinario` (`id_historial`, `id_proceso`, `etapa`, `fecha_registro`, `id_usuario_registra`, `descripcion`, `resultado`) VALUES
(6, 7, 'llamado_escrito',   '2026-06-01 08:30:00', 1, 'Apertura del proceso por falta disciplinaria grave', 'Citación a comité de convivencia'),
(7, 7, 'acondicionamiento', '2026-06-10 14:00:00', 1, 'El comité determina acondicionamiento disciplinario', 'Compromiso de convivencia firmado');

INSERT IGNORE INTO `acta_coordinacion` (`id_acta`, `id_aprendiz`, `id_falta`, `id_proceso`, `tipo_acta`, `numero_acta`, `fecha_expedicion`, `fecha_notificacion_personal`, `fecha_firmeza`, `sancion_descripcion`, `meses_inhabilitacion`, `estado_acta`) VALUES
(7, 13, 7, 7, 'acondicionamiento_disciplinario', 'AC-2026-007', '2026-06-10', '2026-06-12', '2026-06-17', 'Acondicionamiento disciplinario con compromiso de convivencia', NULL, 'firme');

-- ---------------------------------------------------------------------
-- Historia C (muy grave, cerrada): Sara Mejía (aprendiz 12, ficha 6)
-- Suplantación en evaluación -> cancelación de matrícula (proceso cerrado)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `llamado_atencion` (`id_llamado`, `id_aprendiz`, `id_instructor`, `id_coordinacion`, `id_usuario_reporta`, `fecha_llamado`, `tipo_llamado`, `categoria`, `asunto`, `descripcion_hechos`, `pruebas_aportadas`, `estado_llamado`, `observaciones`) VALUES
(12, 12, 6, 2, 17, '2026-03-10', 'cancelacion_matricula', 'disciplinario', 'Suplantación en evaluación', 'La aprendiz presentó una evaluación en nombre de otra persona', 'Registros de la plataforma y acta del instructor', 'cerrado', 'Falta muy grave confirmada por el comité');

INSERT IGNORE INTO `falta` (`id_falta`, `id_llamado`, `id_aprendiz`, `id_instructor`, `tipo_falta`, `descripcion_hechos`, `fecha_ocurrencia`, `principio_valor_infringido`, `calificacion_falta`, `estado_falta`) VALUES
(8, 12, 12, 6, 'disciplinaria', 'Suplantación de identidad en evaluación del trimestre', '2026-03-10', 'Honestidad académica', 'muy_grave', 'resuelto');

INSERT IGNORE INTO `proceso_disciplinario` (`id_proceso`, `id_aprendiz`, `id_llamado`, `etapa_actual`, `fecha_inicio`, `fecha_cierre`, `estado_proceso`, `observaciones`) VALUES
(8, 12, 12, 'cancelacion_matricula', '2026-03-15', '2026-05-30', 'cerrado', 'Proceso cerrado con cancelación de matrícula por falta muy grave');

INSERT IGNORE INTO `historial_proceso_disciplinario` (`id_historial`, `id_proceso`, `etapa`, `fecha_registro`, `id_usuario_registra`, `descripcion`, `resultado`) VALUES
(8,  8, 'llamado_escrito',       '2026-03-15 09:00:00', 1, 'Apertura del proceso por falta muy grave: suplantación en evaluación', 'Citación inmediata a comité'),
(9,  8, 'acondicionamiento',     '2026-04-10 10:00:00', 1, 'El comité evalúa los descargos; la falta queda confirmada',            'Se recomienda cancelación de matrícula'),
(10, 8, 'cancelacion_matricula', '2026-05-30 11:00:00', 1, 'Se expide el acta de cancelación de matrícula',                        'Proceso cerrado con cancelación');

INSERT IGNORE INTO `acta_coordinacion` (`id_acta`, `id_aprendiz`, `id_falta`, `id_proceso`, `tipo_acta`, `numero_acta`, `fecha_expedicion`, `fecha_notificacion_personal`, `fecha_firmeza`, `sancion_descripcion`, `meses_inhabilitacion`, `estado_acta`) VALUES
(8, 12, 8, 8, 'cancelacion_disciplinaria', 'AC-2026-008', '2026-05-30', '2026-06-02', '2026-06-09', 'Cancelación de matrícula por falta muy grave (suplantación)', 6, 'firme');

-- ---------------------------------------------------------------------
-- Historia D (académica, pendiente): Isabella Correa (aprendiz 14, ficha 7)
-- Llamado registrado pendiente de revisión por coordinación
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `llamado_atencion` (`id_llamado`, `id_aprendiz`, `id_instructor`, `id_coordinacion`, `id_usuario_reporta`, `fecha_llamado`, `tipo_llamado`, `categoria`, `asunto`, `descripcion_hechos`, `pruebas_aportadas`, `estado_llamado`, `observaciones`) VALUES
(13, 14, 7, NULL, 18, '2026-06-20', 'llamado_escrito', 'academico', 'No entrega de evidencias del trimestre', 'La aprendiz no cargó las evidencias del proyecto formativo del trimestre', 'Reporte de la plataforma Territorium', 'registrado', NULL);

-- Notificaciones de las historias A-D
INSERT IGNORE INTO `notificacion` (`id_notificacion`, `id_aprendiz`, `id_acta`, `id_falta`, `id_llamado`, `tipo_notificacion`, `fecha_envio`, `medio_envio`, `contenido_resumen`, `estado_notificacion`) VALUES
(9,  10, NULL, NULL, 9,  'comunicado_llamado',           '2026-04-21', 'correo_institucional', 'Primer llamado de atención por inasistencias en abril',                'recibida'),
(10, 10, NULL, NULL, 10, 'citacion',                     '2026-05-19', 'correo_institucional', 'Citación a descargos por reincidencia en inasistencias',               'recibida'),
(11, 10, 6,    NULL, NULL, 'aviso_acta',                 '2026-06-06', 'correo_personal',      'Acta AC-2026-006: acondicionamiento académico con plan de 60 días',    'enviada'),
(12, 13, NULL, NULL, 11, 'citacion',                     '2026-05-29', 'correo_institucional', 'Citación a comité de convivencia por falta disciplinaria',             'recibida'),
(13, 13, 7,    NULL, NULL, 'aviso_acta',                 '2026-06-12', 'correo_institucional', 'Acta AC-2026-007 en firme: acondicionamiento disciplinario',           'recibida'),
(14, 12, NULL, NULL, 12, 'citacion',                     '2026-03-12', 'correo_institucional', 'Citación inmediata a comité por falta muy grave',                      'recibida'),
(15, 12, 8,    NULL, NULL, 'aviso_cancelacion',          '2026-06-02', 'correo_personal',      'Acta AC-2026-008: cancelación de matrícula en firme',                  'recibida'),
(16, 14, NULL, NULL, 13, 'comunicado_llamado',           '2026-06-21', 'correo_institucional', 'Se registró un llamado por no entrega de evidencias del trimestre',    'enviada');
