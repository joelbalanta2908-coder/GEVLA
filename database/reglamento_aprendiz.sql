-- =====================================================================
--  REGLAMENTO DEL APRENDIZ SENA (Acuerdo 09 de 2024)
--  Script listo para copiar y pegar en phpMyAdmin / consola MySQL.
--  Base de datos: sena_disciplinario
--
--  Incluye:
--   1) Columnas necesarias (calificacion en reglamento_articulo,
--      calificacion_falta e id_articulo en llamado_atencion).
--   2) Carga del reglamento: capitulos y articulos.
--   3) Las faltas (articulos) quedan clasificadas por calificacion
--      (leve / grave / muy_grave = gravisima) para el formulario de
--      llamados de atencion del instructor.
--
--  NOTA: si ya ejecutaste "php artisan migrate", las columnas ya existen;
--  los ALTER de abajo usan "IF NOT EXISTS" y no causaran error.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) Columnas requeridas
-- ---------------------------------------------------------------------
ALTER TABLE `reglamento_articulo`
  ADD COLUMN IF NOT EXISTS `calificacion` ENUM('leve','grave','muy_grave') NULL AFTER `titulo`;

ALTER TABLE `llamado_atencion`
  ADD COLUMN IF NOT EXISTS `calificacion_falta` ENUM('leve','grave','muy_grave') NULL AFTER `categoria`;

ALTER TABLE `llamado_atencion`
  ADD COLUMN IF NOT EXISTS `id_articulo` INT(11) NULL AFTER `calificacion_falta`;

-- ---------------------------------------------------------------------
-- 2) Limpieza previa del reglamento (evita duplicados al reejecutar)
-- ---------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM `reglamento_paragrafo`;
DELETE FROM `reglamento_articulo`;
DELETE FROM `reglamento_capitulo`;
DELETE FROM `reglamento_aprendiz`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 3) Reglamento
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_aprendiz` (`id_reglamento`, `nombre_reglamento`, `version`, `fecha_vigencia`, `descripcion`) VALUES
(1, 'Reglamento del Aprendiz SENA', 'Acuerdo 09 de 2024', '2024-11-05', 'Reglamento del Aprendiz del Servicio Nacional de Aprendizaje SENA. Deroga los Acuerdos 07 de 2012, 02 de 2014, 06 de 2023 y 02 de 2024.');

-- ---------------------------------------------------------------------
-- 4) Capitulos
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_capitulo` (`id_capitulo`, `id_reglamento`, `numero_capitulo`, `titulo`, `descripcion`) VALUES
(1, 1, 'I',   'Definiciones', 'Definiciones, alcance y principios orientadores.'),
(2, 1, 'II',  'Derechos del Aprendiz SENA', 'Derechos, reconocimientos formativos y representatividad.'),
(3, 1, 'III', 'Deberes del Aprendiz SENA', 'Deberes y prohibiciones del aprendiz.'),
(4, 1, 'IV',  'Ingreso, Permanencia y Certificacion', 'Reglas de ingreso, formacion, evaluacion y certificacion.'),
(5, 1, 'V',   'Regimen de Faltas, Medidas Formativas, Disciplinarias y Sancionatorias', 'Clasificacion de faltas y procedimiento sancionatorio.');

-- ---------------------------------------------------------------------
-- 5) Articulos generales (sin calificacion)
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_articulo` (`id_articulo`, `id_capitulo`, `numero_articulo`, `titulo`, `calificacion`, `contenido`) VALUES
(1, 1, 'Art. 1',  'Definiciones', NULL, 'Definiciones aplicables al reglamento: formacion profesional integral, comunidad educativa, aspirante, aprendiz y grupo.'),
(2, 2, 'Art. 5',  'Derechos del aprendiz SENA', NULL, 'Conjunto de derechos del aprendiz durante su proceso formativo.'),
(3, 3, 'Art. 8',  'Deberes del aprendiz SENA', NULL, 'Deberes academicos, disciplinarios y administrativos del aprendiz.'),
(4, 3, 'Art. 9',  'Prohibiciones', NULL, 'Conductas prohibidas para los aprendices del SENA.'),
(5, 5, 'Art. 42', 'Calificacion de las faltas', NULL, 'Las faltas se califican como leves, graves o gravisimas, previo cumplimiento del debido proceso.'),
(6, 5, 'Art. 46', 'Tipos de medidas formativas', NULL, 'Medidas formativas academicas y disciplinarias: llamados de atencion y planes de mejoramiento.'),
(7, 5, 'Art. 47', 'Medidas sancionatorias', NULL, 'Condicionamiento de matricula y cancelacion de matricula.');

-- ---------------------------------------------------------------------
-- 6) FALTAS LEVES  (calificacion = 'leve')
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_articulo` (`id_articulo`, `id_capitulo`, `numero_articulo`, `titulo`, `calificacion`, `contenido`) VALUES
(8,  5, 'Art. 8 #5',  'Impuntualidad o inasistencia esporadica a las actividades de formacion', 'leve', 'Hechos contrarios al reglamento que no ponen en riesgo significativo el orden o los derechos de terceros.'),
(9,  5, 'Art. 8 #15', 'No usar los elementos de proteccion personal requeridos', 'leve', 'Incumplimiento de buenas practicas de seguridad y salud en el trabajo sin generar dano.'),
(10, 5, 'Art. 8 #19', 'No portar el carne o los elementos de identificacion institucional', 'leve', 'Falta leve por incumplimiento de los deberes de identificacion.'),
(11, 5, 'Art. 8 #11', 'Uso inadecuado de ambientes, equipos o recursos sin causar dano', 'leve', 'Manejo descuidado de recursos sin detrimento patrimonial.'),
(12, 5, 'Art. 8 #7',  'No justificar oportunamente las inasistencias o incumplimientos', 'leve', 'Incumplimiento del deber de justificar dentro de los terminos del reglamento.'),
(13, 5, 'Art. 8 #4',  'No mantener actualizados los datos en los sistemas del SENA', 'leve', 'Incumplimiento del deber de registrar y actualizar la informacion personal.');

-- ---------------------------------------------------------------------
-- 7) FALTAS GRAVES  (calificacion = 'grave')
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_articulo` (`id_articulo`, `id_capitulo`, `numero_articulo`, `titulo`, `calificacion`, `contenido`) VALUES
(14, 5, 'Art. 9 #4',  'Plagiar trabajos o documentos, o cometer fraude en actividades evaluativas', 'grave', 'Comportamientos que cuestionan los principios y valores y perturban el proceso formativo.'),
(15, 5, 'Art. 9 #2',  'Suplantar identidad en cualquier tramite academico o administrativo', 'grave', 'Afecta de manera significativa a la institucion o a un miembro de la comunidad.'),
(16, 5, 'Art. 9 #3',  'Alterar, falsificar o sustraer documentos del SENA', 'grave', 'Conducta que compromete las normas basicas de convivencia.'),
(17, 5, 'Art. 9 #1',  'Aportar documentos o registrar informacion falsa para obtener un beneficio', 'grave', 'Falta grave por afectar la transparencia de los procesos institucionales.'),
(18, 5, 'Art. 9 #8',  'Usar el nombre, instalaciones o recursos del SENA para fines particulares', 'grave', 'Uso indebido de los recursos institucionales.'),
(19, 5, 'Art. 9 #11', 'Realizar proselitismo politico o religioso en ambientes de formacion', 'grave', 'Conducta que perturba el normal desarrollo de la formacion.'),
(20, 5, 'Art. 9 #12', 'Ingresar o salir por sitios no autorizados saltando muros o cerramientos', 'grave', 'Pone en riesgo la seguridad y el orden institucional.'),
(21, 5, 'Art. 42 #1', 'Reincidencia en la comision de faltas leves', 'grave', 'La acumulacion o reincidencia de faltas leves puede derivar en falta grave.');

-- ---------------------------------------------------------------------
-- 8) FALTAS GRAVISIMAS  (calificacion = 'muy_grave')
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_articulo` (`id_articulo`, `id_capitulo`, `numero_articulo`, `titulo`, `calificacion`, `contenido`) VALUES
(22, 5, 'Art. 9 #6',  'Ingresar, consumir o comercializar bebidas alcoholicas o sustancias psicoactivas', 'muy_grave', 'Conductas que ponen en riesgo la vida y la integridad de las personas.'),
(23, 5, 'Art. 9 #7',  'Ingresar o portar armas u objetos que pongan en riesgo la integridad', 'muy_grave', 'Atenta contra la integridad fisica de la comunidad educativa.'),
(24, 5, 'Art. 9 #9',  'Cometer, ser complice o participe de delitos contra la comunidad o la institucion', 'muy_grave', 'Conducta gravisima que atenta contra los principios del SENA.'),
(25, 5, 'Art. 9 #10', 'Destruir, sustraer o danar intencionalmente bienes del SENA o de terceros', 'muy_grave', 'Dano material que puede causar perjuicios irreparables.'),
(26, 5, 'Art. 9 #14', 'Discriminar a cualquier miembro de la comunidad educativa', 'muy_grave', 'Atenta contra la dignidad y los derechos humanos de las personas.'),
(27, 5, 'Art. 9 #13', 'Hostigamiento, acoso (bullying / mobbing) o acoso sexual', 'muy_grave', 'Conducta que atenta contra la integridad psicologica y moral de las personas.'),
(28, 5, 'Art. 9 #5',  'Difundir contenido violento, pornografico o ilegal por medios del SENA', 'muy_grave', 'Uso de las TIC para causar danos al nombre, honra o derechos ajenos.'),
(29, 5, 'Art. 42 #2', 'Conductas que atenten contra los derechos humanos o la integridad de las personas', 'muy_grave', 'Faltas gravisimas que dan lugar a la cancelacion de matricula.');

-- ---------------------------------------------------------------------
-- 9) Articulos adicionales del reglamento (sin calificacion)
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_articulo` (`id_articulo`, `id_capitulo`, `numero_articulo`, `titulo`, `calificacion`, `contenido`) VALUES
(30, 1, 'Art. 2',  'Alcance del reglamento', NULL, 'Aplica al aspirante en el proceso de ingreso y al aprendiz durante todo su proceso formativo y certificacion, en todas las sedes, jornadas, niveles y modalidades.'),
(31, 1, 'Art. 3',  'Principios orientadores', NULL, 'Autonomia, dignidad, inclusion, enfoque diferencial, enfoque territorial, participacion, desarrollo sostenible y solidaridad.'),
(32, 1, 'Art. 4',  'Centro de Convivencia', NULL, 'Atencion complementaria que brinda alojamiento y alimentacion para aprendices seleccionados.'),
(33, 2, 'Art. 6',  'Reconocimientos formativos', NULL, 'Beneficios que se otorgan para promover la permanencia o valorar actuaciones meritorias: mencion de honor, representar al SENA, practicas y monitorias.'),
(34, 2, 'Art. 7',  'Representatividad de los aprendices', NULL, 'Eleccion de representantes por jornada y modalidad, voceros de grupo y voceros de poblaciones con enfoque diferencial.'),
(35, 4, 'Art. 10', 'Reglas generales de ingreso', NULL, 'Etapas de ingreso: registro, inscripcion, seleccion (cuando aplique) y matricula. Edad minima 14 anos.'),
(36, 4, 'Art. 11', 'Etapa de registro', NULL, 'Procedimiento por el cual el usuario ingresa sus datos personales y de contacto y acepta las politicas de tratamiento de datos.'),
(37, 4, 'Art. 12', 'Etapa de inscripcion', NULL, 'Acto por el cual una persona registrada diligencia el formato de inscripcion y elige el programa de formacion.'),
(38, 4, 'Art. 14', 'Etapa de seleccion', NULL, 'Verificacion de conocimientos, aptitudes y requisitos mediante pruebas para establecer las competencias minimas de ingreso.'),
(39, 4, 'Art. 15', 'Etapa de matricula', NULL, 'Formalizacion del ingreso del aspirante admitido mediante su asentamiento y legalizacion en el sistema.'),
(40, 4, 'Art. 18', 'Novedades durante la formacion', NULL, 'Traslado, aplazamiento, reintegro y retiro voluntario.'),
(41, 4, 'Art. 19', 'Certificacion', NULL, 'Reconocimiento formal de los resultados aprobados por el aprendiz durante su proceso formativo.'),
(42, 4, 'Art. 26', 'El proceso de formacion', NULL, 'Ruta de aprendizaje conformada por la etapa lectiva y la etapa productiva en los casos que aplique.'),
(43, 4, 'Art. 30', 'Desercion', NULL, 'Abandono de la formacion por inasistencias, incumplimiento de la etapa productiva o falta de gestion oportuna de novedades.'),
(44, 4, 'Art. 32', 'Evaluacion del proceso de aprendizaje', NULL, 'Comparacion continua entre aprendiz e instructor segun resultados de aprendizaje; se realiza de forma cualitativa.'),
(45, 4, 'Art. 36', 'Juicios de la Evaluacion', NULL, 'Resultados de aprendizaje expresados como APROBADO o NO APROBADO.'),
(46, 5, 'Art. 39', 'Principios del regimen de faltas', NULL, 'Confidencialidad, debido proceso, culpabilidad e inexistencia de doble sancion.'),
(47, 5, 'Art. 40', 'Medidas disciplinarias, formativas, academicas y/o sancionatorias', NULL, 'Se aplican de acuerdo con la tipificacion de la falta cometida por el aprendiz.'),
(48, 5, 'Art. 41', 'Faltas', NULL, 'Acciones u omisiones que alteran el normal desarrollo de la formacion. Se dividen en academicas y disciplinarias.'),
(49, 5, 'Art. 43', 'Calificacion de la falta', NULL, 'Corresponde a las circunstancias previstas para su realizacion segun los deberes y el marco juridico.'),
(50, 5, 'Art. 44', 'Criterios para calificar la falta', NULL, 'Dano causado, grado de participacion, antecedentes y reparacion del dano.'),
(51, 5, 'Art. 45', 'Medidas formativas', NULL, 'Acciones que se aplican cuando el aprendiz contraria en menor grado el orden academico o disciplinario.'),
(52, 5, 'Art. 48', 'Equipos encargados de la valoracion de las medidas y sanciones', NULL, 'Equipo ejecutor del grupo y Comite de evaluacion y seguimiento del centro de formacion.'),
(53, 5, 'Art. 49', 'Instancias decisorias de las medidas sancionatorias', NULL, 'Primera instancia: Subdireccion del Centro; segunda instancia: Direccion Regional.'),
(54, 5, 'Art. 50', 'Criterios para aplicacion de sanciones', NULL, 'Publicidad, contradiccion, presuncion de inocencia, valoracion de pruebas, motivacion, proporcionalidad e impugnacion.'),
(55, 5, 'Art. 51', 'Procedimiento para la aplicacion de sanciones', NULL, 'Recepcion del informe, citacion al aprendiz, sesion del comite, acto administrativo, notificacion, recurso y firmeza.'),
(56, 5, 'Art. 52', 'Certificacion de conducta y sanciones academicas y disciplinarias', NULL, 'El SENA expide certificados de conducta unicamente por solicitud o autorizacion del aprendiz.'),
(57, 5, 'Art. 53', 'Sujecion a la Constitucion y la ley', NULL, 'El reglamento se aplica con total sujecion a los principios y normas constitucionales y legales vigentes.');

-- ---------------------------------------------------------------------
-- 10) Paragrafos del reglamento
-- ---------------------------------------------------------------------
INSERT INTO `reglamento_paragrafo` (`id_paragrafo`, `id_articulo`, `numero_paragrafo`, `contenido`) VALUES
(1, 33, 'Paragrafo 1', 'Para la asignacion de los reconocimientos el aprendiz debera cumplir con los requisitos y procedimientos que establezca la entidad al momento de hacer la convocatoria.'),
(2, 33, 'Paragrafo 2', 'El aprendiz podra participar en las diferentes convocatorias de reconocimiento; su obtencion no es excluyente entre si.'),
(3, 5,  'Paragrafo 1', 'Para la calificacion de las faltas se tendran en cuenta las causales de atenuacion y las causales de agravacion.'),
(4, 5,  'Paragrafo 2', 'Las conductas que presuntamente constituyan delito tipificado en el Codigo Penal deben denunciarse ante la autoridad competente.'),
(5, 6,  'Paragrafo 1', 'El numero maximo de planes de mejoramiento academico es de dos (2) por fase del proyecto o modulo de la etapa lectiva y dos (2) por etapa productiva.'),
(6, 7,  'Paragrafo 1', 'Un aprendiz puede tener hasta dos (2) condicionamientos de matricula superados; si supera ese numero da lugar a la cancelacion de matricula.'),
(7, 7,  'Paragrafo 2', 'La cancelacion de matricula implica el retiro del programa y para presentarse a un nuevo programa debera esperar seis (6) meses.'),
(8, 36, 'Paragrafo 1', 'El usuario, aspirante o aprendiz debe tener un unico registro en el sistema de gestion academica administrativa, debidamente actualizado.'),
(9, 51, 'Paragrafo 1', 'El numero maximo de planes de mejoramiento disciplinario es de uno (1) por etapa lectiva o modulo y uno (1) por etapa productiva.'),
(10, 55, 'Paragrafo 1', 'Se dara tramite a las quejas o informes anonimos cuando aporten datos que permitan ser verificados o pruebas que demuestren la veracidad de los hechos.');
