<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>GEVLA — Manual de Usuario</title>
    @include('manuales._estilos')
</head>
<body>
@php $logo = \App\Support\ManualPdf::imagen('img/logo-sena-verde.png'); @endphp

{{-- ============================ PORTADA ============================ --}}
<div class="portada">
    <div class="portada-banda"></div>
    <div class="portada-logo">
        @if($logo)<img src="{{ $logo }}" alt="SENA">@else<div style="font-size:34px;font-weight:bold;color:#39A900;">SENA</div>@endif
    </div>
    <div class="portada-kicker">Servicio Nacional de Aprendizaje — SENA</div>
    <div class="portada-titulo">Manual de Usuario</div>
    <div class="portada-sub">GEVLA · Gestión de Llamados de Atención</div>
    <div class="portada-desc">
        Guía práctica para aprendices, instructores y coordinadores sobre el uso del sistema
        de gestión de llamados de atención y procesos disciplinarios del aprendiz SENA.
    </div>
    <div class="portada-meta">
        <b>Sistema:</b> GEVLA — Gestión de Vida Académica y Llamados de Atención<br>
        <b>Dirigido a:</b> Aprendiz · Instructor · Coordinador<br>
        <b>Versión del documento:</b> 1.0<br>
        <b>Clasificación:</b> Uso interno del centro de formación
    </div>
</div>

{{-- ============================ ÍNDICE ============================ --}}
<div class="indice">
    <div class="indice-titulo">Tabla de contenido</div>
    <table class="indice-tabla">
        <tr><td class="n">1</td><td>Introducción</td></tr>
        <tr><td class="n">2</td><td>Objetivo del sistema</td></tr>
        <tr><td class="n">3</td><td>Alcance</td></tr>
        <tr><td class="n">4</td><td>Requisitos mínimos</td></tr>
        <tr><td class="n">5</td><td>Acceso al sistema</td></tr>
        <tr><td class="n">6</td><td>Inicio de sesión</td></tr>
        <tr><td class="n">7</td><td>Recuperación de contraseña</td></tr>
        <tr><td class="n">8</td><td>La opción «Recordarme»</td></tr>
        <tr><td class="n">9</td><td>El panel principal</td></tr>
        <tr><td class="n">10</td><td>Módulos según el rol</td></tr>
        <tr><td class="n">11</td><td>Gestión del perfil</td></tr>
        <tr><td class="n">12</td><td>Firma manuscrita</td></tr>
        <tr><td class="n">13</td><td>Notificaciones</td></tr>
        <tr><td class="n">14</td><td>Gestión de llamados de atención</td></tr>
        <tr><td class="n">15</td><td>Procesos disciplinarios</td></tr>
        <tr><td class="n">16</td><td>Descarga de documentos</td></tr>
        <tr><td class="n">17</td><td>Reportes</td></tr>
        <tr><td class="n">18</td><td>Carga masiva de usuarios</td></tr>
        <tr><td class="n">19</td><td>Reglamento del aprendiz</td></tr>
        <tr><td class="n">20</td><td>Preguntas frecuentes</td></tr>
        <tr><td class="n">21</td><td>Solución de problemas comunes</td></tr>
        <tr><td class="n">22</td><td>Buenas prácticas</td></tr>
        <tr><td class="n">23</td><td>Cierre</td></tr>
    </table>
</div>

{{-- ============================ 1. INTRODUCCIÓN ============================ --}}
<h1 class="capitulo"><span class="num">1.</span> Introducción</h1>
<p>
    <b>GEVLA</b> es el sistema web del SENA para registrar y gestionar los <b>llamados de atención</b>
    y los <b>procesos disciplinarios</b> de los aprendices, de acuerdo con el Reglamento del Aprendiz.
    Reemplaza el manejo manual de formatos en papel por un flujo digital que conserva la trazabilidad
    de cada actuación: quién la registró, cuándo, con qué soportes y con qué firmas.
</p>
<p>
    Este manual explica, paso a paso, cómo usar el sistema según su rol. Está redactado para que
    cualquier usuario —sin conocimientos técnicos avanzados— pueda ingresar, ubicar sus módulos y
    realizar las tareas propias de su perfil. Toda la interfaz del sistema está en español.
</p>
<div class="caja">
    <b>Los tres roles del sistema:</b>
    <ul>
        <li><b>Aprendiz:</b> consulta sus llamados de atención, actas, procesos y notificaciones.</li>
        <li><b>Instructor:</b> registra llamados de atención a los aprendices de sus fichas y gestiona aprendices.</li>
        <li><b>Coordinador:</b> administra usuarios, fichas, programas, actas, procesos, reportes y todos los llamados.</li>
    </ul>
</div>

{{-- ============================ 2. OBJETIVO ============================ --}}
<h1 class="capitulo"><span class="num">2.</span> Objetivo del sistema</h1>
<p>
    Centralizar la gestión disciplinaria y académica del aprendiz SENA en una única plataforma que
    permita registrar llamados de atención, adjuntar pruebas, notificar a los implicados, firmar
    digitalmente los documentos, adelantar procesos disciplinarios y generar reportes, garantizando
    la <b>trazabilidad</b> y la <b>seguridad</b> de la información en cada paso.
</p>
<h2>Objetivos específicos</h2>
<ul>
    <li>Registrar llamados de atención con su categoría, calificación de la falta, artículo del reglamento, asunto, descripción y pruebas.</li>
    <li>Notificar automáticamente al aprendiz por correo cuando se le registra un nuevo llamado.</li>
    <li>Conservar la firma de instructor, coordinador y aprendiz sobre cada documento.</li>
    <li>Adelantar procesos disciplinarios a partir de un llamado de atención.</li>
    <li>Administrar aprendices, instructores, coordinadores, fichas y programas.</li>
    <li>Generar reportes individuales y consolidados en PDF, Excel y Word.</li>
</ul>

{{-- ============================ 3. ALCANCE ============================ --}}
<h1 class="capitulo"><span class="num">3.</span> Alcance</h1>
<p>
    El sistema cubre el ciclo completo de un llamado de atención: registro, notificación, firma,
    consulta, exportación y —cuando corresponde— su escalamiento a proceso disciplinario. Cada rol
    accede únicamente a las funciones que le competen, controladas por el sistema de permisos.
</p>
<div class="caja caja-info">
    <b>Qué NO cubre este manual:</b> la instalación y configuración técnica del servidor (XAMPP,
    base de datos, correo), que se documenta en el <b>Manual Técnico</b>, disponible únicamente para
    instructores y coordinadores.
</div>

{{-- ============================ 4. REQUISITOS ============================ --}}
<h1 class="capitulo"><span class="num">4.</span> Requisitos mínimos</h1>
<p>El sistema es una aplicación web: se usa desde el navegador, no requiere instalación en el equipo del usuario.</p>
<table class="datos">
    <tr><th>Elemento</th><th>Requisito recomendado</th></tr>
    <tr><td class="clave">Equipo</td><td>Computador de escritorio, portátil, tableta o teléfono con conexión a internet o a la red local del centro.</td></tr>
    <tr class="alt"><td class="clave">Navegador</td><td>Google Chrome, Microsoft Edge o Mozilla Firefox en su versión reciente. La interfaz es responsive y se adapta a pantallas pequeñas.</td></tr>
    <tr><td class="clave">Conexión</td><td>Acceso a la dirección del servidor donde está publicado GEVLA (la proporciona el centro de formación).</td></tr>
    <tr class="alt"><td class="clave">Lector de PDF</td><td>Para abrir los documentos y reportes descargados (Acrobat Reader, el visor del navegador o similar).</td></tr>
    <tr><td class="clave">Credenciales</td><td>Correo y contraseña asignados o creados por el coordinador/instructor.</td></tr>
</table>

{{-- ============================ 5. ACCESO ============================ --}}
<h1 class="capitulo"><span class="num">5.</span> Acceso al sistema</h1>
<p>
    Abra el navegador e ingrese la dirección (URL) del sistema proporcionada por su centro de formación.
    Aparecerá la pantalla de inicio de sesión con la identidad institucional del SENA y el nombre del sistema, <b>GEVLA</b>.
</p>
@include('manuales._captura', ['archivo' => '01-login.png', 'pie' => 'Figura 5.1 — Pantalla de inicio de sesión'])
<div class="caja caja-alerta">
    <b>Importante:</b> si no tiene una cuenta, solicítela a su coordinador (o a su instructor, en el caso de
    los aprendices). El sistema no permite el auto-registro: las cuentas las crea el personal autorizado.
</div>

{{-- ============================ 6. INICIO DE SESIÓN ============================ --}}
<h1 class="capitulo"><span class="num">6.</span> Inicio de sesión</h1>
<p>El acceso al sistema se realiza <b>únicamente con el correo electrónico</b> y la contraseña.</p>
<table class="pasos">
    <tr><td class="paso">1</td><td>En el campo <b>Correo</b>, escriba la dirección de correo registrada en el sistema.</td></tr>
    <tr><td class="paso">2</td><td>En el campo <b>Contraseña</b>, escriba su clave. Puede usar el ícono del ojo para ver u ocultar lo que digita.</td></tr>
    <tr><td class="paso">3</td><td>Si lo desea, marque la casilla <b>Recordarme</b> (ver capítulo 8).</td></tr>
    <tr><td class="paso">4</td><td>Presione el botón <b>Iniciar sesión</b>.</td></tr>
</table>
<p>
    Al ingresar, el sistema lo lleva al panel correspondiente a su rol. Si tiene más de un rol asignado
    (por ejemplo, instructor y coordinador), podrá alternar entre ellos desde el menú de su cuenta.
</p>
<div class="caja">
    <b>Contraseña inicial:</b> cuando el coordinador o el instructor crea su cuenta, la contraseña inicial
    corresponde a su <b>número de documento</b>. Se recomienda cambiarla en el primer ingreso desde
    <b>Mi cuenta → Cambiar contraseña</b> (ver capítulo 11).
</div>

{{-- ============================ 7. RECUPERAR CONTRASEÑA ============================ --}}
<h1 class="capitulo"><span class="num">7.</span> Recuperación de contraseña</h1>
<p>Si olvidó su contraseña, el sistema le permite restablecerla mediante un código enviado a su correo.</p>
<table class="pasos">
    <tr><td class="paso">1</td><td>En la pantalla de inicio de sesión, presione el enlace <b>¿Olvidaste tu contraseña?</b></td></tr>
    <tr><td class="paso">2</td><td>Escriba su <b>correo</b> registrado y solicite el envío del código.</td></tr>
    <tr><td class="paso">3</td><td>Revise su bandeja de entrada (y la carpeta de correo no deseado). Copie el <b>código</b> recibido.</td></tr>
    <tr><td class="paso">4</td><td>Ingrese el código en el sistema para validarlo.</td></tr>
    <tr><td class="paso">5</td><td>Defina y confirme su <b>nueva contraseña</b>.</td></tr>
</table>
@include('manuales._captura', ['archivo' => '02-recuperar.png', 'pie' => 'Figura 7.1 — Solicitud de recuperación de contraseña'])
<div class="caja caja-alerta">
    <b>El código tiene vigencia limitada.</b> Si expira o no llega, solicite uno nuevo. Si el problema
    persiste, contacte a su coordinador para verificar que el correo esté bien registrado.
</div>

{{-- ============================ 8. RECORDARME ============================ --}}
<h1 class="capitulo"><span class="num">8.</span> La opción «Recordarme»</h1>
<p>
    La casilla <b>Recordarme</b>, en la pantalla de inicio de sesión, mantiene su sesión iniciada en ese
    dispositivo aunque cierre el navegador, para no tener que escribir sus credenciales cada vez.
</p>
<ul>
    <li><b>Cuándo usarla:</b> solo en dispositivos personales y de confianza.</li>
    <li><b>Cuándo NO usarla:</b> en computadores compartidos, de la biblioteca o de un aula.</li>
    <li>La opción se apoya en una cookie segura del navegador; <b>nunca</b> guarda su contraseña.</li>
    <li>Al presionar <b>Cerrar sesión</b>, el sistema borra ese recuerdo y deberá volver a autenticarse.</li>
</ul>
<div class="caja caja-info">
    <b>Cierre automático:</b> por seguridad, el sistema puede cerrar la sesión al cerrar la pestaña del
    navegador. Si marcó «Recordarme», este cierre automático no se aplica en ese dispositivo.
</div>

{{-- ============================ 9. PANEL PRINCIPAL ============================ --}}
<h1 class="capitulo"><span class="num">9.</span> El panel principal</h1>
<p>Tras iniciar sesión verá el panel (Dashboard) de su rol. Sus elementos comunes son:</p>
<table class="datos">
    <tr><th>Zona</th><th>Descripción</th></tr>
    <tr><td class="clave">Barra lateral</td><td>Menú de navegación con los módulos disponibles para su rol. Se contrae en pantallas pequeñas.</td></tr>
    <tr class="alt"><td class="clave">Encabezado</td><td>Título de la sección actual y el menú de <b>Mi cuenta</b> (perfil, cambio de rol y cerrar sesión).</td></tr>
    <tr><td class="clave">Indicadores</td><td>Tarjetas con cifras clave (llamados, procesos, fichas, etc.) según el rol.</td></tr>
    <tr class="alt"><td class="clave">Contenido</td><td>Tablas, formularios y paneles de la sección seleccionada. Los listados muestran un máximo de 10 registros por página.</td></tr>
</table>
@include('manuales._captura', ['archivo' => '03-dashboard.png', 'pie' => 'Figura 9.1 — Panel principal (Dashboard)'])

{{-- ============================ 10. MÓDULOS SEGÚN ROL ============================ --}}
<h1 class="capitulo"><span class="num">10.</span> Módulos según el rol</h1>
<p>Cada rol dispone de un conjunto de módulos en su barra lateral. La siguiente tabla resume la navegación real del sistema:</p>

<h2>Aprendiz</h2>
<table class="datos">
    <tr><th>Módulo</th><th>Para qué sirve</th></tr>
    <tr><td class="clave">Mi Dashboard</td><td>Resumen de su situación: llamados, actas y procesos.</td></tr>
    <tr class="alt"><td class="clave">Mis Llamados</td><td>Consulta el detalle de cada llamado de atención recibido y descarga su documento.</td></tr>
    <tr><td class="clave">Mis Actas</td><td>Actas de coordinación asociadas a su proceso.</td></tr>
    <tr class="alt"><td class="clave">Mis Procesos</td><td>Estado de sus procesos disciplinarios.</td></tr>
    <tr><td class="clave">Notificaciones</td><td>Avisos del sistema.</td></tr>
    <tr class="alt"><td class="clave">Reglamento</td><td>Consulta del Reglamento del Aprendiz con buscador.</td></tr>
</table>

<h2>Instructor</h2>
<table class="datos">
    <tr><th>Módulo</th><th>Para qué sirve</th></tr>
    <tr><td class="clave">Mi Dashboard</td><td>Resumen de su actividad e indicadores.</td></tr>
    <tr class="alt"><td class="clave">Llamados de atención</td><td>Registra y consulta llamados a los aprendices de sus fichas.</td></tr>
    <tr><td class="clave">Mis Fichas</td><td>Fichas donde imparte clases y sus aprendices.</td></tr>
    <tr class="alt"><td class="clave">Aprendices</td><td>Ver, crear y asociar aprendices en sus fichas; descargar su reporte.</td></tr>
    <tr><td class="clave">Procesos</td><td>Procesos disciplinarios asociados a sus aprendices.</td></tr>
    <tr class="alt"><td class="clave">Notificaciones · Reglamento</td><td>Avisos del sistema y consulta del reglamento.</td></tr>
</table>

<h2>Coordinador</h2>
<table class="datos">
    <tr><th>Módulo</th><th>Para qué sirve</th></tr>
    <tr><td class="clave">Dashboard</td><td>Panel con indicadores y gráficas de la gestión.</td></tr>
    <tr class="alt"><td class="clave">Aprendices · Instructores · Coordinadores</td><td>Administración de usuarios: crear, editar, activar/inactivar y eliminar.</td></tr>
    <tr><td class="clave">Fichas · Programas</td><td>Gestión de fichas de caracterización y programas de formación.</td></tr>
    <tr class="alt"><td class="clave">Llamados de atención</td><td>Todos los llamados del centro, con buscador de reporte por aprendiz.</td></tr>
    <tr><td class="clave">Actas de coordinación</td><td>Gestión y descarga de actas.</td></tr>
    <tr class="alt"><td class="clave">Procesos disciplinarios</td><td>Apertura y seguimiento de procesos.</td></tr>
    <tr><td class="clave">Reportes</td><td>Reportes consolidados en PDF, Excel y Word.</td></tr>
    <tr class="alt"><td class="clave">Reglamento</td><td>Consulta del Reglamento del Aprendiz.</td></tr>
</table>

{{-- ============================ 11. PERFIL ============================ --}}
<h1 class="capitulo"><span class="num">11.</span> Gestión del perfil</h1>
<p>Desde el menú <b>Mi cuenta</b> (esquina superior derecha) puede administrar su información personal.</p>
<h2>Ver y editar datos</h2>
<table class="pasos">
    <tr><td class="paso">1</td><td>Abra <b>Mi cuenta → Mi perfil</b> para ver sus datos.</td></tr>
    <tr><td class="paso">2</td><td>Presione <b>Editar</b> para actualizar nombres, apellidos o correo, y guarde los cambios.</td></tr>
</table>
<h2>Cambiar contraseña</h2>
<table class="pasos">
    <tr><td class="paso">1</td><td>En su perfil, abra la opción <b>Cambiar contraseña</b>.</td></tr>
    <tr><td class="paso">2</td><td>Escriba su <b>contraseña actual</b>, la <b>nueva contraseña</b> y su confirmación.</td></tr>
    <tr><td class="paso">3</td><td>Guarde. La próxima vez ingresará con la nueva clave.</td></tr>
</table>
<h2>Cerrar sesión</h2>
<p>La opción <b>Cerrar sesión</b> está disponible en el menú <b>Mi cuenta</b> en todas las vistas del sistema. Úsela siempre al terminar, especialmente en equipos compartidos.</p>
@include('manuales._captura', ['archivo' => '06-perfil.png', 'pie' => 'Figura 11.1 — Mi perfil y sus opciones'])

{{-- ============================ 12. FIRMA ============================ --}}
<h1 class="capitulo"><span class="num">12.</span> Firma manuscrita</h1>
<p>
    El sistema incorpora las firmas en los documentos de los llamados de atención. Cada usuario registra
    su firma <b>una sola vez</b> desde su perfil y el sistema la inserta automáticamente cuando firma.
</p>
<table class="pasos">
    <tr><td class="paso">1</td><td>Abra <b>Mi perfil</b> y ubique la sección <b>Firma</b>.</td></tr>
    <tr><td class="paso">2</td><td>Suba una imagen de su firma (fondo claro, nítida). Puede reemplazarla o eliminarla cuando lo requiera.</td></tr>
    <tr><td class="paso">3</td><td>Al firmar un llamado, el sistema toma esa firma y la estampa en el documento con la fecha y hora.</td></tr>
</table>
<div class="caja caja-alerta">
    <b>Si no ha registrado su firma</b>, el sistema le impedirá firmar y le indicará que la registre primero
    en Mi perfil. Su firma es privada: solo usted puede verla y gestionarla.
</div>

{{-- ============================ 13. NOTIFICACIONES ============================ --}}
<h1 class="capitulo"><span class="num">13.</span> Notificaciones</h1>
<p>El módulo <b>Notificaciones</b> reúne los avisos del sistema. Además:</p>
<ul>
    <li>Cuando se registra un <b>nuevo llamado de atención</b>, el aprendiz recibe un <b>correo electrónico</b> con los datos del llamado (nombre, fecha, tipo, categoría, calificación, artículo del reglamento, asunto, descripción y las pruebas, si las hay).</li>
    <li>Cuando cambia el <b>estado</b> de un llamado, el aprendiz también puede recibir un aviso por correo.</li>
    <li>Las notificaciones dentro del sistema pueden marcarse como leídas.</li>
</ul>
<div class="caja caja-info">
    <b>Nota de privacidad:</b> los correos se envían únicamente a partir de nuevos llamados registrados en
    el sistema; nunca se reenvían correos de llamados antiguos.
</div>

{{-- ============================ 14. LLAMADOS ============================ --}}
<h1 class="capitulo"><span class="num">14.</span> Gestión de llamados de atención</h1>

<h2>Registrar un llamado (instructor / coordinador)</h2>
<table class="pasos">
    <tr><td class="paso">1</td><td>Ingrese al módulo <b>Llamados de atención</b> y presione <b>Nuevo llamado</b>.</td></tr>
    <tr><td class="paso">2</td><td>Seleccione el <b>aprendiz</b> (buscador con sugerencias) y complete: <b>categoría</b> (académico o disciplinario), <b>calificación de la falta</b>, <b>artículo del reglamento</b>, <b>asunto</b>, <b>fecha</b> y <b>descripción de los hechos</b>.</td></tr>
    <tr><td class="paso">3</td><td>En <b>pruebas aportadas</b> puede escribir un texto y adjuntar una o varias <b>fotos</b>.</td></tr>
    <tr><td class="paso">4</td><td>Guarde. El sistema registra el llamado y notifica al aprendiz por correo.</td></tr>
</table>
@include('manuales._captura', ['archivo' => '04-nuevo-llamado.png', 'pie' => 'Figura 14.1 — Formulario de nuevo llamado de atención'])

<h2>Consultar un llamado</h2>
<p>
    En el listado, use <b>Ver detalle</b> para abrir el llamado completo: datos del aprendiz, del instructor,
    categoría, calificación, artículo, asunto, descripción, pruebas (texto y fotos), estado y firmas.
    Los aprendices consultan sus propios llamados desde <b>Mis Llamados</b>.
</p>
@include('manuales._captura', ['archivo' => '07-detalle-llamado.png', 'pie' => 'Figura 14.2 — Detalle de un llamado de atención con sus firmas'])

<h2>Firmar un llamado</h2>
<p>
    Desde el detalle, la opción de <b>firmar</b> incorpora su firma registrada al documento. Cada rol
    (instructor, coordinador y aprendiz) firma en su espacio correspondiente. La firma queda registrada
    con fecha y hora para efectos de trazabilidad.
</p>

<h2>Estados de un llamado</h2>
<table class="datos">
    <tr><th>Estado</th><th>Significado</th></tr>
    <tr><td class="clave">Registrado</td><td>El llamado fue creado en el sistema.</td></tr>
    <tr class="alt"><td class="clave">En revisión</td><td>Está siendo revisado.</td></tr>
    <tr><td class="clave">Notificado</td><td>Se notificó al aprendiz.</td></tr>
    <tr class="alt"><td class="clave">Cerrado</td><td>El llamado concluyó.</td></tr>
    <tr><td class="clave">Cancelado</td><td>El llamado fue anulado.</td></tr>
</table>

{{-- ============================ 15. PROCESOS ============================ --}}
<h1 class="capitulo"><span class="num">15.</span> Procesos disciplinarios</h1>
<p>
    Un proceso disciplinario puede iniciarse desde el detalle de un llamado de atención o como un proceso nuevo.
    Cuando se inicia <b>desde un llamado</b>, el aprendiz queda fijo (no puede cambiarse), pues corresponde al
    aprendiz de ese llamado.
</p>
<table class="datos">
    <tr><th>Etapa</th><th>Descripción</th></tr>
    <tr><td class="clave">Llamado escrito</td><td>Primera etapa formal del proceso.</td></tr>
    <tr class="alt"><td class="clave">Condicionamiento</td><td>Etapa de condicionamiento de la matrícula.</td></tr>
    <tr><td class="clave">Cancelación de matrícula</td><td>Etapa final de cancelación.</td></tr>
    <tr class="alt"><td class="clave">Finalizado</td><td>El proceso concluyó.</td></tr>
</table>
<p>El aprendiz puede consultar el estado de sus procesos desde el módulo <b>Mis Procesos</b>.</p>

{{-- ============================ 16. DOCUMENTOS ============================ --}}
<h1 class="capitulo"><span class="num">16.</span> Descarga de documentos</h1>
<p>El sistema genera documentos oficiales en PDF, listos para imprimir o archivar:</p>
<ul>
    <li><b>Documento del llamado de atención</b> (con las firmas registradas), descargable por el instructor, el coordinador y el propio aprendiz.</li>
    <li><b>Acta de coordinación</b> (formato oficial), desde el módulo de actas.</li>
    <li><b>Reglamento del Aprendiz</b> en PDF, desde el módulo de reglamento.</li>
</ul>
<p>Los formatos oficiales se generan del lado del servidor con una herramienta libre (Dompdf) y se descargan directamente al equipo del usuario.</p>

{{-- ============================ 17. REPORTES ============================ --}}
<h1 class="capitulo"><span class="num">17.</span> Reportes</h1>
<h2>Reporte individual del aprendiz</h2>
<p>
    Desde el <b>perfil del aprendiz</b>, el botón <b>Descargar reporte completo</b> genera un PDF con sus datos
    y todo su historial: llamados, actas y procesos. Está disponible para el coordinador y para el instructor
    (este último solo para los aprendices de sus fichas).
</p>
<h2>Buscador de reporte por aprendiz</h2>
<p>
    En el módulo <b>Llamados de atención</b> del coordinador, el panel <b>Reporte por aprendiz</b> permite buscar
    a un aprendiz por nombre o documento y descargar su reporte completo o abrir su perfil.
</p>
<h2>Reportes consolidados (coordinador)</h2>
<p>
    El módulo <b>Reportes</b> exporta los llamados, actas y procesos del centro en <b>PDF</b> (imprimible),
    <b>Excel</b> (.xlsx) y <b>Word</b> (.doc), con la posibilidad de filtrar por ficha.
</p>
@include('manuales._captura', ['archivo' => '05-reportes.png', 'pie' => 'Figura 17.1 — Módulo de reportes'])

{{-- ============================ 18. CARGA MASIVA ============================ --}}
<h1 class="capitulo"><span class="num">18.</span> Carga masiva de usuarios</h1>
<p>
    Para crear muchos usuarios a la vez, el sistema ofrece la <b>carga masiva por Excel</b>. El coordinador
    puede importar aprendices, instructores y coordinadores; el instructor solo aprendices de sus fichas.
</p>
<table class="pasos">
    <tr><td class="paso">1</td><td>En el panel de carga masiva, descargue la <b>plantilla</b> correspondiente al tipo de usuario.</td></tr>
    <tr><td class="paso">2</td><td>Diligencie la plantilla respetando las listas desplegables y el formato de cada columna.</td></tr>
    <tr><td class="paso">3</td><td>Suba el archivo. El sistema valida todo el contenido antes de importar.</td></tr>
    <tr><td class="paso">4</td><td>Si hay errores, se muestra un <b>reporte por fila</b> y no se importa nada (todo o nada). Corrija y vuelva a subir.</td></tr>
</table>
<div class="caja caja-alerta">
    <b>Cada plantilla solo sirve para su propio tipo.</b> Si sube la plantilla de aprendices en la carga de
    instructores (o viceversa), el sistema lo rechaza. El número de documento admite letras y números,
    entre 6 y 20 caracteres según el tipo, sin espacios ni caracteres especiales.
</div>
@include('manuales._captura', ['archivo' => '08-carga-masiva.png', 'pie' => 'Figura 18.1 — Panel de carga masiva por Excel'])

{{-- ============================ 19. REGLAMENTO ============================ --}}
<h1 class="capitulo"><span class="num">19.</span> Reglamento del aprendiz</h1>
<p>
    El módulo <b>Reglamento</b> permite consultar el Reglamento del Aprendiz directamente en el sistema, con un
    <b>buscador</b> que resalta las coincidencias. También puede descargarlo en PDF. Es útil para ubicar el
    artículo o falta al registrar un llamado de atención.
</p>

{{-- ============================ 20. FAQ ============================ --}}
<h1 class="capitulo"><span class="num">20.</span> Preguntas frecuentes</h1>
<table class="datos">
    <tr><th>Pregunta</th><th>Respuesta</th></tr>
    <tr><td class="clave">¿Con qué inicio sesión?</td><td>Únicamente con su correo electrónico y contraseña.</td></tr>
    <tr class="alt"><td class="clave">¿Cuál es mi contraseña inicial?</td><td>Su número de documento. Cámbiela en el primer ingreso.</td></tr>
    <tr><td class="clave">Olvidé mi contraseña.</td><td>Use «¿Olvidaste tu contraseña?» y siga el proceso con el código enviado a su correo (capítulo 7).</td></tr>
    <tr class="alt"><td class="clave">No puedo firmar un llamado.</td><td>Registre primero su firma en Mi perfil (capítulo 12).</td></tr>
    <tr><td class="clave">No me llegó el correo del llamado.</td><td>Revise el correo no deseado y confirme con su coordinador que su correo esté bien registrado.</td></tr>
    <tr class="alt"><td class="clave">¿Puedo usar el sistema en el celular?</td><td>Sí, la interfaz es responsive y se adapta a pantallas pequeñas.</td></tr>
</table>

{{-- ============================ 21. PROBLEMAS ============================ --}}
<h1 class="capitulo"><span class="num">21.</span> Solución de problemas comunes</h1>
<table class="datos">
    <tr><th>Situación</th><th>Qué hacer</th></tr>
    <tr><td class="clave">«Credenciales incorrectas»</td><td>Verifique que el correo y la contraseña sean correctos. Recuerde que la contraseña distingue mayúsculas.</td></tr>
    <tr class="alt"><td class="clave">La sesión se cierra sola</td><td>Es una medida de seguridad al cerrar la pestaña. Marque «Recordarme» en su equipo personal para evitarlo.</td></tr>
    <tr><td class="clave">No veo un módulo</td><td>Los módulos dependen de su rol. Si cree que le falta un permiso, consulte con su coordinador.</td></tr>
    <tr class="alt"><td class="clave">El PDF no se descarga</td><td>Revise que el navegador no esté bloqueando la descarga y que tenga un lector de PDF.</td></tr>
    <tr><td class="clave">La página se ve desalineada</td><td>Actualice con Ctrl+F5 para recargar los estilos; use un navegador actualizado.</td></tr>
</table>

{{-- ============================ 22. BUENAS PRÁCTICAS ============================ --}}
<h1 class="capitulo"><span class="num">22.</span> Buenas prácticas</h1>
<ul>
    <li>Cambie su contraseña inicial y no la comparta con nadie.</li>
    <li>Cierre sesión al terminar, sobre todo en equipos compartidos.</li>
    <li>Use «Recordarme» solo en dispositivos personales.</li>
    <li>Registre los llamados con información veraz, clara y completa; adjunte las pruebas cuando existan.</li>
    <li>Verifique el artículo del reglamento antes de registrar la falta (use el módulo Reglamento).</li>
    <li>Descargue y archive los documentos oficiales generados por el sistema.</li>
</ul>

{{-- ============================ 23. CIERRE ============================ --}}
<h1 class="capitulo"><span class="num">23.</span> Cierre</h1>
<p>
    Con este manual usted cuenta con la guía necesaria para operar GEVLA de acuerdo con su rol. El buen uso
    del sistema garantiza la trazabilidad y la transparencia de la gestión disciplinaria y académica del
    aprendiz SENA.
</p>
<p>
    Ante fallos o dudas que este manual no resuelva, contacte al área de soporte interno de su centro de
    formación. Para aspectos técnicos y de mantenimiento, consulte el <b>Manual Técnico</b> (disponible para
    instructores y coordinadores).
</p>
<div class="caja">
    <b>GEVLA</b> — Sistema de gestión de llamados de atención y procesos disciplinarios del aprendiz.<br>
    Servicio Nacional de Aprendizaje — SENA · Documento de uso interno.
</div>
</body>
</html>
