-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-06-2026 a las 02:43:58
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sena_disciplinario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acta_coordinacion`
--

CREATE TABLE `acta_coordinacion` (
  `id_acta` int(11) NOT NULL,
  `id_aprendiz` int(11) NOT NULL,
  `id_falta` int(11) NOT NULL,
  `id_proceso` int(11) DEFAULT NULL,
  `tipo_acta` enum('acondicionamiento_academico','cancelacion_academica','acondicionamiento_disciplinario','cancelacion_disciplinaria') NOT NULL,
  `numero_acta` varchar(30) NOT NULL,
  `fecha_expedicion` date NOT NULL,
  `fecha_notificacion_personal` date DEFAULT NULL,
  `fecha_firmeza` date DEFAULT NULL,
  `sancion_descripcion` text DEFAULT NULL,
  `meses_inhabilitacion` int(11) DEFAULT NULL,
  `estado_acta` enum('expedido','notificado','firme') NOT NULL DEFAULT 'expedido'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `acta_coordinacion`
--

INSERT INTO `acta_coordinacion` (`id_acta`, `id_aprendiz`, `id_falta`, `id_proceso`, `tipo_acta`, `numero_acta`, `fecha_expedicion`, `fecha_notificacion_personal`, `fecha_firmeza`, `sancion_descripcion`, `meses_inhabilitacion`, `estado_acta`) VALUES
(1, 1, 1, 1, 'acondicionamiento_academico', 'AC-2024-001', '2024-04-10', '2024-04-12', '2024-04-17', 'Plan de mejoramiento académico por 30 días', NULL, 'firme'),
(2, 2, 2, 2, 'acondicionamiento_disciplinario', 'AC-2024-002', '2024-05-01', '2024-05-03', '2024-05-08', 'Acondicionamiento disciplinario por celular', NULL, 'notificado'),
(3, 3, 3, 3, 'cancelacion_academica', 'AC-2024-003', '2024-05-21', '2024-05-22', '2024-05-27', 'Cancelación de matrícula por bajo rendimiento', NULL, 'expedido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aprendiz`
--

CREATE TABLE `aprendiz` (
  `id_aprendiz` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `correo_institucional` varchar(120) NOT NULL,
  `correo_personal` varchar(120) DEFAULT NULL,
  `estado_academico` enum('en_formacion','aplazado','cancelado','certificado') NOT NULL DEFAULT 'en_formacion',
  `tiene_apoyo_sostenimiento` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aprendiz`
--

INSERT INTO `aprendiz` (`id_aprendiz`, `id_usuario`, `correo_institucional`, `correo_personal`, `estado_academico`, `tiene_apoyo_sostenimiento`) VALUES
(1, 3, 'jdiaz@aprendiz.sena.edu.co', 'jdiaz@gmail.com', 'en_formacion', 1),
(2, 4, 'atorres@aprendiz.sena.edu.co', 'atorres@hotmail.com', 'en_formacion', 0),
(3, 5, 'lmartinez@aprendiz.sena.edu.co', 'lmartinez@yahoo.com', 'aplazado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordinacion`
--

CREATE TABLE `coordinacion` (
  `id_coordinacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `dependencia` varchar(120) DEFAULT NULL,
  `estado_coordinacion` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coordinacion`
--

INSERT INTO `coordinacion` (`id_coordinacion`, `id_usuario`, `cargo`, `dependencia`, `estado_coordinacion`) VALUES
(1, 1, 'Coordinador Académico', 'Coordinación Académica', 'activo'),
(2, 8, 'Coordinador Misional', 'Coordinación Misional', 'activo'),
(3, 6, 'Subdirector', 'Subdirección de Centro', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `falta`
--

CREATE TABLE `falta` (
  `id_falta` int(11) NOT NULL,
  `id_llamado` int(11) NOT NULL,
  `id_aprendiz` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL,
  `tipo_falta` enum('academica','disciplinaria') NOT NULL,
  `descripcion_hechos` text DEFAULT NULL,
  `fecha_ocurrencia` date NOT NULL,
  `principio_valor_infringido` varchar(150) DEFAULT NULL,
  `calificacion_falta` enum('leve','grave','muy_grave') NOT NULL,
  `estado_falta` enum('en_proceso','resuelto','archivado') NOT NULL DEFAULT 'en_proceso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `falta`
--

INSERT INTO `falta` (`id_falta`, `id_llamado`, `id_aprendiz`, `id_instructor`, `tipo_falta`, `descripcion_hechos`, `fecha_ocurrencia`, `principio_valor_infringido`, `calificacion_falta`, `estado_falta`) VALUES
(1, 1, 1, 1, 'academica', 'Inasistencia a 5 sesiones sin justificación', '2024-03-15', 'Responsabilidad y cumplimiento', 'leve', 'en_proceso'),
(2, 2, 2, 1, 'disciplinaria', 'Uso de celular en evaluación', '2024-04-10', 'Honestidad académica', 'grave', 'en_proceso'),
(3, 3, 3, 2, 'academica', 'Incumplimiento en entrega de proyecto', '2024-05-02', 'Compromiso formativo', 'leve', 'en_proceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ficha`
--

CREATE TABLE `ficha` (
  `id_ficha` int(11) NOT NULL,
  `id_programa` int(11) NOT NULL,
  `id_instructor_lider` int(11) NOT NULL,
  `numero_ficha` varchar(20) NOT NULL,
  `modalidad` enum('presencial','virtual','distancia') NOT NULL,
  `estado_ficha` enum('en_ejecucion','terminada','cancelada') NOT NULL DEFAULT 'en_ejecucion',
  `fecha_inicio` date NOT NULL,
  `fecha_fin_programada` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ficha`
--

INSERT INTO `ficha` (`id_ficha`, `id_programa`, `id_instructor_lider`, `numero_ficha`, `modalidad`, `estado_ficha`, `fecha_inicio`, `fecha_fin_programada`) VALUES
(1, 1, 1, '2758934', 'presencial', 'en_ejecucion', '2023-03-06', '2025-03-06'),
(2, 1, 1, '2891023', 'virtual', 'en_ejecucion', '2023-08-07', '2025-08-07'),
(3, 2, 2, '3012456', 'presencial', 'en_ejecucion', '2024-01-15', '2025-07-15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_proceso_disciplinario`
--

CREATE TABLE `historial_proceso_disciplinario` (
  `id_historial` int(11) NOT NULL,
  `id_proceso` int(11) NOT NULL,
  `etapa` enum('llamado_escrito','acondicionamiento','cancelacion_matricula') NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario_registra` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `resultado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_proceso_disciplinario`
--

INSERT INTO `historial_proceso_disciplinario` (`id_historial`, `id_proceso`, `etapa`, `fecha_registro`, `id_usuario_registra`, `descripcion`, `resultado`) VALUES
(1, 1, 'llamado_escrito', '2024-03-20 10:00:00', 2, 'Apertura del proceso por inasistencias', 'Proceso iniciado'),
(2, 1, 'acondicionamiento', '2024-04-01 09:30:00', 1, 'Aprendiz citado a descargos el 5 de abril', 'Citación entregada'),
(3, 3, 'llamado_escrito', '2024-05-05 08:00:00', 2, 'Registro del llamado por proyecto no entregado', 'Llamado registrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructor`
--

CREATE TABLE `instructor` (
  `id_instructor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `codigo_instructor` varchar(30) NOT NULL,
  `area_formacion` varchar(120) DEFAULT NULL,
  `estado_instructor` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructor`
--

INSERT INTO `instructor` (`id_instructor`, `id_usuario`, `codigo_instructor`, `area_formacion`, `estado_instructor`) VALUES
(1, 2, 'INS-001', 'Tecnología e Informática', 'activo'),
(2, 6, 'INS-002', 'Gestión Empresarial', 'activo'),
(3, 8, 'INS-003', 'Idiomas', 'inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `llamado_atencion`
--

CREATE TABLE `llamado_atencion` (
  `id_llamado` int(11) NOT NULL,
  `id_aprendiz` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL,
  `id_coordinacion` int(11) DEFAULT NULL,
  `id_usuario_reporta` int(11) NOT NULL,
  `fecha_llamado` date NOT NULL,
  `tipo_llamado` enum('llamado_escrito','acondicionamiento','cancelacion_matricula') NOT NULL,
  `categoria` enum('academico','disciplinario') NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `descripcion_hechos` text DEFAULT NULL,
  `pruebas_aportadas` text DEFAULT NULL,
  `estado_llamado` enum('registrado','en_revision','notificado','cerrado','cancelado') NOT NULL DEFAULT 'registrado',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `llamado_atencion`
--

INSERT INTO `llamado_atencion` (`id_llamado`, `id_aprendiz`, `id_instructor`, `id_coordinacion`, `id_usuario_reporta`, `fecha_llamado`, `tipo_llamado`, `categoria`, `asunto`, `descripcion_hechos`, `pruebas_aportadas`, `estado_llamado`, `observaciones`) VALUES
(1, 1, 1, 1, 2, '2024-03-15', 'llamado_escrito', 'academico', 'Inasistencias reiteradas', 'El aprendiz acumuló 5 inasistencias en marzo', 'Lista de asistencia marzo', 'notificado', 'Pendiente respuesta del aprendiz'),
(2, 2, 1, NULL, 2, '2024-04-10', 'acondicionamiento', 'disciplinario', 'Comportamiento inadecuado', 'Uso de dispositivo móvil durante evaluación', 'Acta de evaluación', 'en_revision', 'Se citó a descargos'),
(3, 3, 2, 2, 2, '2024-05-02', 'llamado_escrito', 'academico', 'Entrega tardía de proyecto', 'No entregó el proyecto final en la fecha establecida', 'Cronograma de actividades', 'registrado', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula`
--

CREATE TABLE `matricula` (
  `id_matricula` int(11) NOT NULL,
  `id_aprendiz` int(11) NOT NULL,
  `id_ficha` int(11) NOT NULL,
  `fecha_matricula` date NOT NULL,
  `estado_matricula` enum('activa','aplazada','retirada','cancelada') NOT NULL DEFAULT 'activa',
  `es_vocero` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_vocero` enum('principal','suplente','no_es_vocero') NOT NULL DEFAULT 'no_es_vocero'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `matricula`
--

INSERT INTO `matricula` (`id_matricula`, `id_aprendiz`, `id_ficha`, `fecha_matricula`, `estado_matricula`, `es_vocero`, `tipo_vocero`) VALUES
(1, 1, 1, '2023-03-06', 'activa', 1, 'principal'),
(2, 2, 1, '2023-03-06', 'activa', 0, 'no_es_vocero'),
(3, 3, 2, '2023-08-07', 'aplazada', 0, 'no_es_vocero');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `id_aprendiz` int(11) NOT NULL,
  `id_acta` int(11) DEFAULT NULL,
  `id_falta` int(11) DEFAULT NULL,
  `id_llamado` int(11) DEFAULT NULL,
  `tipo_notificacion` enum('citacion','comunicado_llamado','comunicado_acondicionamiento','aviso_cancelacion','aviso_acta') NOT NULL,
  `fecha_envio` date NOT NULL,
  `medio_envio` enum('correo_institucional','correo_personal','aviso_fisico','pagina_web') NOT NULL,
  `contenido_resumen` text DEFAULT NULL,
  `estado_notificacion` enum('enviada','recibida','no_entregada') NOT NULL DEFAULT 'enviada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`id_notificacion`, `id_aprendiz`, `id_acta`, `id_falta`, `id_llamado`, `tipo_notificacion`, `fecha_envio`, `medio_envio`, `contenido_resumen`, `estado_notificacion`) VALUES
(1, 1, NULL, NULL, 1, 'citacion', '2024-03-18', 'correo_institucional', 'Citación para descargos el 22 de marzo', 'recibida'),
(2, 2, NULL, 2, NULL, 'comunicado_llamado', '2024-04-12', 'correo_personal', 'Notificación de falta disciplinaria registrada', 'enviada'),
(3, 1, 1, NULL, NULL, 'aviso_acta', '2024-04-12', 'correo_institucional', 'Acta de coordinación AC-2024-001 expedida', 'recibida');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso_disciplinario`
--

CREATE TABLE `proceso_disciplinario` (
  `id_proceso` int(11) NOT NULL,
  `id_aprendiz` int(11) NOT NULL,
  `id_llamado` int(11) NOT NULL,
  `etapa_actual` enum('llamado_escrito','acondicionamiento','cancelacion_matricula','finalizado') NOT NULL DEFAULT 'llamado_escrito',
  `fecha_inicio` date NOT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado_proceso` enum('activo','cerrado','anulado') NOT NULL DEFAULT 'activo',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proceso_disciplinario`
--

INSERT INTO `proceso_disciplinario` (`id_proceso`, `id_aprendiz`, `id_llamado`, `etapa_actual`, `fecha_inicio`, `fecha_cierre`, `estado_proceso`, `observaciones`) VALUES
(1, 1, 1, 'acondicionamiento', '2024-03-20', NULL, 'activo', 'En espera de descargos del aprendiz'),
(2, 2, 2, 'llamado_escrito', '2024-04-15', NULL, 'activo', 'Citación enviada para audiencia'),
(3, 3, 3, 'llamado_escrito', '2024-05-05', '2024-05-20', 'cerrado', 'Aprendiz subsanó la falta con entrega tardía');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa_formacion`
--

CREATE TABLE `programa_formacion` (
  `id_programa` int(11) NOT NULL,
  `codigo_programa` varchar(20) NOT NULL,
  `nombre_programa` varchar(150) NOT NULL,
  `nivel` enum('tecnico','tecnologo','auxiliar','operario') NOT NULL,
  `duracion_meses` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `programa_formacion`
--

INSERT INTO `programa_formacion` (`id_programa`, `codigo_programa`, `nombre_programa`, `nivel`, `duracion_meses`) VALUES
(1, '228106', 'Análisis y Desarrollo de Software', 'tecnologo', 24),
(2, '122121', 'Contabilidad y Finanzas', 'tecnico', 18),
(3, '631201', 'Gestión Logística', 'tecnologo', 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglamento_aprendiz`
--

CREATE TABLE `reglamento_aprendiz` (
  `id_reglamento` int(11) NOT NULL,
  `nombre_reglamento` varchar(200) NOT NULL,
  `version` varchar(30) NOT NULL,
  `fecha_vigencia` date NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reglamento_aprendiz`
--

INSERT INTO `reglamento_aprendiz` (`id_reglamento`, `nombre_reglamento`, `version`, `fecha_vigencia`, `descripcion`) VALUES
(1, 'Reglamento del Aprendiz SENA', 'v2023', '2023-01-01', 'Normas generales de convivencia y disciplina'),
(2, 'Reglamento Interno de Formación', 'v2022', '2022-01-01', 'Normas académicas y de asistencia'),
(3, 'Reglamento de Bienestar', 'v2021', '2021-06-01', 'Lineamientos de apoyo y sostenimiento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglamento_articulo`
--

CREATE TABLE `reglamento_articulo` (
  `id_articulo` int(11) NOT NULL,
  `id_capitulo` int(11) NOT NULL,
  `numero_articulo` varchar(20) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reglamento_articulo`
--

INSERT INTO `reglamento_articulo` (`id_articulo`, `id_capitulo`, `numero_articulo`, `titulo`, `contenido`) VALUES
(1, 2, 'Art. 15', 'Faltas Leves', 'Se considera falta leve el incumplimiento de actividades...'),
(2, 2, 'Art. 16', 'Faltas Graves', 'Son faltas graves las que vulneren la integridad...'),
(3, 3, 'Art. 7', 'Porcentaje Asistencia', 'El aprendiz debe cumplir mínimo el 80% de asistencia...');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglamento_capitulo`
--

CREATE TABLE `reglamento_capitulo` (
  `id_capitulo` int(11) NOT NULL,
  `id_reglamento` int(11) NOT NULL,
  `numero_capitulo` varchar(20) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reglamento_capitulo`
--

INSERT INTO `reglamento_capitulo` (`id_capitulo`, `id_reglamento`, `numero_capitulo`, `titulo`, `descripcion`) VALUES
(1, 1, 'I', 'Disposiciones Generales', 'Objeto, ámbito y definiciones'),
(2, 1, 'III', 'Faltas Disciplinarias', 'Clasificación y procedimiento'),
(3, 2, 'II', 'Asistencia y Puntualidad', 'Deberes del aprendiz en horario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglamento_paragrafo`
--

CREATE TABLE `reglamento_paragrafo` (
  `id_paragrafo` int(11) NOT NULL,
  `id_articulo` int(11) NOT NULL,
  `numero_paragrafo` varchar(20) NOT NULL,
  `contenido` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reglamento_paragrafo`
--

INSERT INTO `reglamento_paragrafo` (`id_paragrafo`, `id_articulo`, `numero_paragrafo`, `contenido`) VALUES
(1, 1, 'Pár. 1', 'Las faltas leves acumuladas pueden derivar en falta grave...'),
(2, 2, 'Pár. 1', 'Se exceptúan causas de fuerza mayor debidamente justificadas...'),
(3, 3, 'Pár. 1', 'En incapacidad médica se mantendrá el apoyo de sostenimiento...');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(3, 'Coordinador'),
(2, 'Instructor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `tipo_documento` enum('CC','TI','CE','PEP') NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado_usuario` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `numero_documento`, `tipo_documento`, `nombres`, `apellidos`, `correo`, `telefono`, `username`, `password_hash`, `estado_usuario`, `ultimo_acceso`, `fecha_creacion`) VALUES
(1, '1020304050', 'CC', 'Carlos', 'Pérez', 'cperez@sena.edu.co', '3001234567', 'cperez', '$2b$10$abc...', 'activo', '2024-06-10 08:30:00', '2022-01-15 09:00:00'),
(2, '987654321', 'CC', 'María', 'López', 'mlopez@sena.edu.co', '3109876543', 'mlopez', '$2b$10$def...', 'activo', '2024-06-09 14:00:00', '2021-08-20 10:00:00'),
(3, '112233445', 'TI', 'Juan', 'Díaz', 'jdiaz@correo.com', '3204455667', 'jdiaz', '$2b$10$ghi...', 'activo', '2024-06-08 11:15:00', '2023-03-01 08:00:00'),
(4, '223344556', 'CC', 'Ana', 'Torres', 'atorres@correo.com', '3115566778', 'atorres', '$2b$10$jkl...', 'activo', '2024-06-07 09:00:00', '2023-03-01 08:00:00'),
(5, '334455667', 'CC', 'Luis', 'Martínez', 'lmartinez@correo.com', '3226677889', 'lmartinez', '$2b$10$mno...', 'activo', '2024-06-06 10:00:00', '2023-08-07 08:00:00'),
(6, '445566778', 'CC', 'Pedro', 'Gómez', 'pgomez@sena.edu.co', '3337788990', 'pgomez', '$2b$10$pqr...', 'activo', '2024-06-05 08:00:00', '2020-05-10 09:00:00'),
(8, '556677889', 'CC', 'Lucía', 'Ramírez', 'lramirez@sena.edu.co', '3448899001', 'lramirez', '$2b$10$stu...', 'activo', '2024-06-04 08:00:00', '2019-02-01 09:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

CREATE TABLE `usuario_rol` (
  `id_usuario_rol` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_asignacion` enum('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_rol`
--

INSERT INTO `usuario_rol` (`id_usuario_rol`, `id_usuario`, `id_rol`, `fecha_asignacion`, `estado_asignacion`) VALUES
(1, 1, 1, '2022-01-15 09:00:00', 'activa'),
(2, 2, 2, '2021-08-20 10:00:00', 'activa'),
(3, 8, 3, '2019-02-01 09:00:00', 'activa');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acta_coordinacion`
--
ALTER TABLE `acta_coordinacion`
  ADD PRIMARY KEY (`id_acta`),
  ADD UNIQUE KEY `numero_acta` (`numero_acta`),
  ADD KEY `fk_acta_aprendiz` (`id_aprendiz`),
  ADD KEY `fk_acta_falta` (`id_falta`),
  ADD KEY `fk_acta_proceso` (`id_proceso`);

--
-- Indices de la tabla `aprendiz`
--
ALTER TABLE `aprendiz`
  ADD PRIMARY KEY (`id_aprendiz`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `coordinacion`
--
ALTER TABLE `coordinacion`
  ADD PRIMARY KEY (`id_coordinacion`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `falta`
--
ALTER TABLE `falta`
  ADD PRIMARY KEY (`id_falta`),
  ADD KEY `fk_falta_llamado` (`id_llamado`),
  ADD KEY `fk_falta_aprendiz` (`id_aprendiz`),
  ADD KEY `fk_falta_instructor` (`id_instructor`);

--
-- Indices de la tabla `ficha`
--
ALTER TABLE `ficha`
  ADD PRIMARY KEY (`id_ficha`),
  ADD UNIQUE KEY `numero_ficha` (`numero_ficha`),
  ADD KEY `fk_ficha_programa` (`id_programa`),
  ADD KEY `fk_ficha_instructor` (`id_instructor_lider`);

--
-- Indices de la tabla `historial_proceso_disciplinario`
--
ALTER TABLE `historial_proceso_disciplinario`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_proceso` (`id_proceso`),
  ADD KEY `fk_historial_usuario` (`id_usuario_registra`);

--
-- Indices de la tabla `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`id_instructor`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `codigo_instructor` (`codigo_instructor`);

--
-- Indices de la tabla `llamado_atencion`
--
ALTER TABLE `llamado_atencion`
  ADD PRIMARY KEY (`id_llamado`),
  ADD KEY `fk_llamado_aprendiz` (`id_aprendiz`),
  ADD KEY `fk_llamado_instructor` (`id_instructor`),
  ADD KEY `fk_llamado_coordinacion` (`id_coordinacion`),
  ADD KEY `fk_llamado_usuario_reporta` (`id_usuario_reporta`);

--
-- Indices de la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`id_matricula`),
  ADD KEY `fk_matricula_aprendiz` (`id_aprendiz`),
  ADD KEY `fk_matricula_ficha` (`id_ficha`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notificacion_aprendiz` (`id_aprendiz`),
  ADD KEY `fk_notificacion_acta` (`id_acta`),
  ADD KEY `fk_notificacion_falta` (`id_falta`),
  ADD KEY `fk_notificacion_llamado` (`id_llamado`);

--
-- Indices de la tabla `proceso_disciplinario`
--
ALTER TABLE `proceso_disciplinario`
  ADD PRIMARY KEY (`id_proceso`),
  ADD KEY `fk_proceso_aprendiz` (`id_aprendiz`),
  ADD KEY `fk_proceso_llamado` (`id_llamado`);

--
-- Indices de la tabla `programa_formacion`
--
ALTER TABLE `programa_formacion`
  ADD PRIMARY KEY (`id_programa`),
  ADD UNIQUE KEY `codigo_programa` (`codigo_programa`);

--
-- Indices de la tabla `reglamento_aprendiz`
--
ALTER TABLE `reglamento_aprendiz`
  ADD PRIMARY KEY (`id_reglamento`);

--
-- Indices de la tabla `reglamento_articulo`
--
ALTER TABLE `reglamento_articulo`
  ADD PRIMARY KEY (`id_articulo`),
  ADD KEY `fk_articulo_capitulo` (`id_capitulo`);

--
-- Indices de la tabla `reglamento_capitulo`
--
ALTER TABLE `reglamento_capitulo`
  ADD PRIMARY KEY (`id_capitulo`),
  ADD KEY `fk_capitulo_reglamento` (`id_reglamento`);

--
-- Indices de la tabla `reglamento_paragrafo`
--
ALTER TABLE `reglamento_paragrafo`
  ADD PRIMARY KEY (`id_paragrafo`),
  ADD KEY `fk_paragrafo_articulo` (`id_articulo`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD PRIMARY KEY (`id_usuario_rol`),
  ADD KEY `fk_usuario_rol_usuario` (`id_usuario`),
  ADD KEY `fk_usuario_rol_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acta_coordinacion`
--
ALTER TABLE `acta_coordinacion`
  MODIFY `id_acta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `aprendiz`
--
ALTER TABLE `aprendiz`
  MODIFY `id_aprendiz` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `coordinacion`
--
ALTER TABLE `coordinacion`
  MODIFY `id_coordinacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `falta`
--
ALTER TABLE `falta`
  MODIFY `id_falta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ficha`
--
ALTER TABLE `ficha`
  MODIFY `id_ficha` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `historial_proceso_disciplinario`
--
ALTER TABLE `historial_proceso_disciplinario`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `instructor`
--
ALTER TABLE `instructor`
  MODIFY `id_instructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `llamado_atencion`
--
ALTER TABLE `llamado_atencion`
  MODIFY `id_llamado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `matricula`
--
ALTER TABLE `matricula`
  MODIFY `id_matricula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proceso_disciplinario`
--
ALTER TABLE `proceso_disciplinario`
  MODIFY `id_proceso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `programa_formacion`
--
ALTER TABLE `programa_formacion`
  MODIFY `id_programa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reglamento_aprendiz`
--
ALTER TABLE `reglamento_aprendiz`
  MODIFY `id_reglamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reglamento_articulo`
--
ALTER TABLE `reglamento_articulo`
  MODIFY `id_articulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reglamento_capitulo`
--
ALTER TABLE `reglamento_capitulo`
  MODIFY `id_capitulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reglamento_paragrafo`
--
ALTER TABLE `reglamento_paragrafo`
  MODIFY `id_paragrafo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  MODIFY `id_usuario_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acta_coordinacion`
--
ALTER TABLE `acta_coordinacion`
  ADD CONSTRAINT `fk_acta_aprendiz` FOREIGN KEY (`id_aprendiz`) REFERENCES `aprendiz` (`id_aprendiz`),
  ADD CONSTRAINT `fk_acta_falta` FOREIGN KEY (`id_falta`) REFERENCES `falta` (`id_falta`),
  ADD CONSTRAINT `fk_acta_proceso` FOREIGN KEY (`id_proceso`) REFERENCES `proceso_disciplinario` (`id_proceso`);

--
-- Filtros para la tabla `aprendiz`
--
ALTER TABLE `aprendiz`
  ADD CONSTRAINT `fk_aprendiz_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `coordinacion`
--
ALTER TABLE `coordinacion`
  ADD CONSTRAINT `fk_coordinacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `falta`
--
ALTER TABLE `falta`
  ADD CONSTRAINT `fk_falta_aprendiz` FOREIGN KEY (`id_aprendiz`) REFERENCES `aprendiz` (`id_aprendiz`),
  ADD CONSTRAINT `fk_falta_instructor` FOREIGN KEY (`id_instructor`) REFERENCES `instructor` (`id_instructor`),
  ADD CONSTRAINT `fk_falta_llamado` FOREIGN KEY (`id_llamado`) REFERENCES `llamado_atencion` (`id_llamado`);

--
-- Filtros para la tabla `ficha`
--
ALTER TABLE `ficha`
  ADD CONSTRAINT `fk_ficha_instructor` FOREIGN KEY (`id_instructor_lider`) REFERENCES `instructor` (`id_instructor`),
  ADD CONSTRAINT `fk_ficha_programa` FOREIGN KEY (`id_programa`) REFERENCES `programa_formacion` (`id_programa`);

--
-- Filtros para la tabla `historial_proceso_disciplinario`
--
ALTER TABLE `historial_proceso_disciplinario`
  ADD CONSTRAINT `fk_historial_proceso` FOREIGN KEY (`id_proceso`) REFERENCES `proceso_disciplinario` (`id_proceso`),
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `instructor`
--
ALTER TABLE `instructor`
  ADD CONSTRAINT `fk_instructor_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `llamado_atencion`
--
ALTER TABLE `llamado_atencion`
  ADD CONSTRAINT `fk_llamado_aprendiz` FOREIGN KEY (`id_aprendiz`) REFERENCES `aprendiz` (`id_aprendiz`),
  ADD CONSTRAINT `fk_llamado_coordinacion` FOREIGN KEY (`id_coordinacion`) REFERENCES `coordinacion` (`id_coordinacion`),
  ADD CONSTRAINT `fk_llamado_instructor` FOREIGN KEY (`id_instructor`) REFERENCES `instructor` (`id_instructor`),
  ADD CONSTRAINT `fk_llamado_usuario_reporta` FOREIGN KEY (`id_usuario_reporta`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD CONSTRAINT `fk_matricula_aprendiz` FOREIGN KEY (`id_aprendiz`) REFERENCES `aprendiz` (`id_aprendiz`),
  ADD CONSTRAINT `fk_matricula_ficha` FOREIGN KEY (`id_ficha`) REFERENCES `ficha` (`id_ficha`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `fk_notificacion_acta` FOREIGN KEY (`id_acta`) REFERENCES `acta_coordinacion` (`id_acta`),
  ADD CONSTRAINT `fk_notificacion_aprendiz` FOREIGN KEY (`id_aprendiz`) REFERENCES `aprendiz` (`id_aprendiz`),
  ADD CONSTRAINT `fk_notificacion_falta` FOREIGN KEY (`id_falta`) REFERENCES `falta` (`id_falta`),
  ADD CONSTRAINT `fk_notificacion_llamado` FOREIGN KEY (`id_llamado`) REFERENCES `llamado_atencion` (`id_llamado`);

--
-- Filtros para la tabla `proceso_disciplinario`
--
ALTER TABLE `proceso_disciplinario`
  ADD CONSTRAINT `fk_proceso_aprendiz` FOREIGN KEY (`id_aprendiz`) REFERENCES `aprendiz` (`id_aprendiz`),
  ADD CONSTRAINT `fk_proceso_llamado` FOREIGN KEY (`id_llamado`) REFERENCES `llamado_atencion` (`id_llamado`);

--
-- Filtros para la tabla `reglamento_articulo`
--
ALTER TABLE `reglamento_articulo`
  ADD CONSTRAINT `fk_articulo_capitulo` FOREIGN KEY (`id_capitulo`) REFERENCES `reglamento_capitulo` (`id_capitulo`);

--
-- Filtros para la tabla `reglamento_capitulo`
--
ALTER TABLE `reglamento_capitulo`
  ADD CONSTRAINT `fk_capitulo_reglamento` FOREIGN KEY (`id_reglamento`) REFERENCES `reglamento_aprendiz` (`id_reglamento`);

--
-- Filtros para la tabla `reglamento_paragrafo`
--
ALTER TABLE `reglamento_paragrafo`
  ADD CONSTRAINT `fk_paragrafo_articulo` FOREIGN KEY (`id_articulo`) REFERENCES `reglamento_articulo` (`id_articulo`);

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `fk_usuario_rol_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`),
  ADD CONSTRAINT `fk_usuario_rol_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
