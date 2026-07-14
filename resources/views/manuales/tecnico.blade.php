<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>GEVLA — Manual Técnico</title>
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
    <div class="portada-titulo">Manual Técnico</div>
    <div class="portada-sub">GEVLA · Gestión de Llamados de Atención</div>
    <div class="portada-desc">
        Documentación técnica de la arquitectura, la base de datos, la seguridad, el despliegue y el
        mantenimiento del sistema GEVLA. Dirigido al personal técnico responsable del soporte.
    </div>
    <div class="portada-meta">
        <b>Sistema:</b> GEVLA — Gestión de Vida Académica y Llamados de Atención<br>
        <b>Dirigido a:</b> Instructor · Coordinador (soporte técnico)<br>
        <b>Plataforma:</b> Laravel 12 · PHP 8.2+ · MariaDB (XAMPP)<br>
        <b>Versión del documento:</b> 1.0
    </div>
</div>

{{-- ============================ ÍNDICE ============================ --}}
<div class="indice">
    <div class="indice-titulo">Tabla de contenido</div>
    <table class="indice-tabla">
        <tr><td class="n">1</td><td>Alcance del documento</td></tr>
        <tr><td class="n">2</td><td>Arquitectura del sistema</td></tr>
        <tr><td class="n">3</td><td>Tecnologías utilizadas</td></tr>
        <tr><td class="n">4</td><td>Estructura del proyecto</td></tr>
        <tr><td class="n">5</td><td>Modelo de datos</td></tr>
        <tr><td class="n">6</td><td>Módulos del sistema</td></tr>
        <tr><td class="n">7</td><td>Roles y permisos</td></tr>
        <tr><td class="n">8</td><td>Autenticación y manejo de sesiones</td></tr>
        <tr><td class="n">9</td><td>Seguridad implementada</td></tr>
        <tr><td class="n">10</td><td>Validaciones</td></tr>
        <tr><td class="n">11</td><td>Flujo del sistema</td></tr>
        <tr><td class="n">12</td><td>Carga masiva de usuarios</td></tr>
        <tr><td class="n">13</td><td>Firmas digitales</td></tr>
        <tr><td class="n">14</td><td>Generación de documentos y reportes</td></tr>
        <tr><td class="n">15</td><td>Notificaciones por correo</td></tr>
        <tr><td class="n">16</td><td>Base de datos, descarga e implementación</td></tr>
        <tr><td class="n">17</td><td>Mantenimiento</td></tr>
        <tr><td class="n">18</td><td>Procedimientos para futuras actualizaciones</td></tr>
        <tr><td class="n">19</td><td>Recomendaciones</td></tr>
    </table>
</div>

{{-- ============================ 1. ALCANCE ============================ --}}
<h1 class="capitulo"><span class="num">1.</span> Alcance del documento</h1>
<p>
    Este manual describe los aspectos técnicos de <b>GEVLA</b> que permiten al personal de soporte entender
    su funcionamiento, instalarlo, mantenerlo y evolucionarlo. Documenta la arquitectura, las tecnologías,
    la estructura del proyecto, el modelo de datos, la seguridad, el despliegue y los procedimientos de
    actualización. No incluye el uso funcional del sistema, que se cubre en el <b>Manual de Usuario</b>.
</p>

{{-- ============================ 2. ARQUITECTURA ============================ --}}
<h1 class="capitulo"><span class="num">2.</span> Arquitectura del sistema</h1>
<p>
    GEVLA es una aplicación web construida con el framework <b>Laravel 12</b> siguiendo el patrón
    <b>MVC</b> (Modelo–Vista–Controlador). El servidor procesa las peticiones, aplica la lógica de negocio
    y devuelve vistas Blade renderizadas del lado del servidor. La interfaz se apoya en Tailwind CSS y en
    Alpine.js para interactividad ligera.
</p>
<table class="diagrama">
    <tr>
        <td>Navegador<br>(Blade + Tailwind + Alpine.js)</td>
        <td class="flecha">⇄</td>
        <td class="neutro">Rutas<br>(routes/web.php)</td>
        <td class="flecha">→</td>
        <td>Middleware<br>(Auth · Rol · Sesión)</td>
    </tr>
</table>
<table class="diagrama">
    <tr>
        <td>Controladores<br>(App\Http\Controllers)</td>
        <td class="flecha">⇄</td>
        <td>Modelos Eloquent<br>(App\Models)</td>
        <td class="flecha">⇄</td>
        <td class="neutro">Base de datos<br>MariaDB</td>
    </tr>
</table>
<div class="pie-diagrama">Figura 2.1 — Flujo MVC de una petición en GEVLA</div>
<p>
    La lógica reutilizable se agrupa en clases de soporte (<code>App\Support</code>) y en <i>traits</i>
    (<code>App\Http\Controllers\Concerns</code>), lo que mantiene los controladores delgados y evita
    duplicación entre roles.
</p>

{{-- ============================ 3. TECNOLOGÍAS ============================ --}}
<h1 class="capitulo"><span class="num">3.</span> Tecnologías utilizadas</h1>
<table class="datos">
    <tr><th>Capa</th><th>Tecnología</th><th>Rol</th></tr>
    <tr><td class="clave">Framework</td><td>Laravel 12 (PHP 8.2+)</td><td>Backend, ruteo, ORM, validación, autenticación.</td></tr>
    <tr class="alt"><td class="clave">Lenguaje</td><td>PHP 8.2+</td><td>Lógica del servidor.</td></tr>
    <tr><td class="clave">Vistas</td><td>Blade</td><td>Plantillas renderizadas en el servidor.</td></tr>
    <tr class="alt"><td class="clave">Estilos</td><td>Tailwind CSS v4 (Vite)</td><td>Diseño responsive; compilado con <code>npm run build</code>.</td></tr>
    <tr><td class="clave">Interactividad</td><td>Alpine.js 3</td><td>Comboboxes, paneles plegables, validación en vivo.</td></tr>
    <tr class="alt"><td class="clave">Gráficas</td><td>Chart.js 4.4</td><td>Indicadores del dashboard del coordinador.</td></tr>
    <tr><td class="clave">Base de datos</td><td>MariaDB (XAMPP)</td><td>Motor InnoDB.</td></tr>
    <tr class="alt"><td class="clave">PDF</td><td>Dompdf 3.1 (MIT)</td><td>Documentos oficiales, reportes y manuales.</td></tr>
    <tr><td class="clave">Excel</td><td>PhpSpreadsheet 5.8 (MIT)</td><td>Plantillas y carga masiva.</td></tr>
    <tr class="alt"><td class="clave">Correo</td><td>PHPMailer (SMTP)</td><td>Notificaciones y recuperación de contraseña.</td></tr>
</table>
<div class="caja caja-info">
    <b>Software libre:</b> todas las librerías utilizadas son de código abierto y licencia permisiva
    (MIT / LGPL). No hay dependencias de pago.
</div>

{{-- ============================ 4. ESTRUCTURA ============================ --}}
<h1 class="capitulo"><span class="num">4.</span> Estructura del proyecto</h1>
<p>El proyecto sigue la organización estándar de Laravel, con algunas convenciones propias de GEVLA:</p>
<table class="datos">
    <tr><th>Carpeta</th><th>Contenido</th></tr>
    <tr><td class="clave">app/Http/Controllers</td><td>Controladores por dominio (Coordinación, Instructor, Llamado, Proceso, Perfil, Importación, Manual, etc.).</td></tr>
    <tr class="alt"><td class="clave">app/Http/Controllers/Concerns</td><td>Traits compartidos, p. ej. <code>CreaUsuarios</code> (alta de personas y roles).</td></tr>
    <tr><td class="clave">app/Http/Middleware</td><td><code>EnsureUserHasRole</code>, <code>SeguridadSesion</code>, <code>ShareActiveRole</code>.</td></tr>
    <tr class="alt"><td class="clave">app/Models</td><td>Modelos Eloquent (Usuario, Aprendiz, Instructor, Coordinacion, LlamadoAtencion, Ficha, Matricula, etc.).</td></tr>
    <tr><td class="clave">app/Support</td><td>Servicios: <code>DocumentoLlamado</code>, <code>ManualPdf</code>, <code>ReporteExcel</code>, <code>ImportadorUsuarios</code>, <code>PlantillaImportacion</code>, <code>Firmas</code>, <code>CorreoLlamado</code>, <code>CorreoRecuperacion</code>, <code>Busqueda</code>, <code>Roles</code>, <code>Texto</code>, <code>PruebasLlamado</code>.</td></tr>
    <tr class="alt"><td class="clave">resources/views</td><td>Vistas Blade organizadas por rol y módulo; <code>manuales/</code> contiene los PDF de este manual.</td></tr>
    <tr><td class="clave">routes/web.php</td><td>Definición de rutas, agrupadas por middleware de autenticación y rol.</td></tr>
    <tr class="alt"><td class="clave">database/sql</td><td>Módulos SQL idempotentes: <code>modulo_fichas</code>, <code>modulo_firmas</code>, <code>modulo_login</code>, <code>modulo_notificaciones</code>, <code>modulo_tipos_documento</code>.</td></tr>
    <tr><td class="clave">librerias/phpmailer</td><td>PHPMailer incluido en el repositorio (autoload por Composer).</td></tr>
    <tr class="alt"><td class="clave">public/</td><td>Punto de entrada, assets compilados (<code>build/</code>), imágenes, formatos oficiales y capturas de los manuales.</td></tr>
</table>

{{-- ============================ 5. MODELO DE DATOS ============================ --}}
<h1 class="capitulo"><span class="num">5.</span> Modelo de datos</h1>
<p>Base de datos relacional en MariaDB (InnoDB). Tablas principales del dominio:</p>
<table class="datos">
    <tr><th>Tabla</th><th>Descripción</th></tr>
    <tr><td class="clave">usuario</td><td>Cuenta base: nombres, apellidos, tipo y número de documento, correo, <code>username</code>, <code>password_hash</code>, estado, <code>remember_token</code>, último acceso.</td></tr>
    <tr class="alt"><td class="clave">rol</td><td>Catálogo de roles (Aprendiz, Instructor, Coordinador) y su pivote con usuario (asignación con estado).</td></tr>
    <tr><td class="clave">aprendiz / instructor / coordinacion</td><td>Perfiles específicos, cada uno con <code>id_usuario</code>.</td></tr>
    <tr class="alt"><td class="clave">programa_formacion / ficha</td><td>Programas y fichas de caracterización.</td></tr>
    <tr><td class="clave">matricula / ficha_instructor</td><td>Vínculo de aprendices e instructores con fichas.</td></tr>
    <tr class="alt"><td class="clave">llamado_atencion</td><td>Llamado: aprendiz, instructor, coordinación, fecha, tipo, categoría, calificación, artículo, asunto, descripción, pruebas, estado.</td></tr>
    <tr><td class="clave">falta</td><td>Faltas asociadas al aprendiz / llamado.</td></tr>
    <tr class="alt"><td class="clave">firma_llamado</td><td>Firmas por llamado: <code>id_llamado</code>, <code>id_usuario</code>, <code>rol_firma</code>, fecha; UNIQUE(id_llamado, rol_firma).</td></tr>
    <tr><td class="clave">proceso_disciplinario</td><td>Procesos: aprendiz, llamado de origen, etapa y estado.</td></tr>
    <tr class="alt"><td class="clave">acta_coordinacion</td><td>Actas de coordinación.</td></tr>
    <tr><td class="clave">notificacion / notificacion_usuario</td><td>Avisos del sistema y su relación con usuarios.</td></tr>
    <tr class="alt"><td class="clave">reglamento_*</td><td>Reglamento del aprendiz (capítulo, artículo, parágrafo).</td></tr>
    <tr><td class="clave">historial_*</td><td>Trazabilidad de líder de ficha y de procesos disciplinarios.</td></tr>
    <tr class="alt"><td class="clave">sessions / password_reset_tokens / cache / jobs</td><td>Tablas de infraestructura de Laravel.</td></tr>
</table>
<div class="caja">
    <b>Convención de módulos SQL:</b> las funcionalidades incrementales (firmas, notificaciones, login,
    tipos de documento, fichas) se entregan como scripts <code>database/sql/modulo_*.sql</code> idempotentes,
    importables desde phpMyAdmin. El sistema degrada con elegancia si un módulo no está instalado
    (verificación con <code>Schema::hasTable</code>).
</div>

{{-- ============================ 6. MÓDULOS ============================ --}}
<h1 class="capitulo"><span class="num">6.</span> Módulos del sistema</h1>
<table class="datos">
    <tr><th>Módulo</th><th>Controlador principal</th></tr>
    <tr><td class="clave">Autenticación</td><td>Auth (login, logout, recuperación de contraseña).</td></tr>
    <tr class="alt"><td class="clave">Gestión de usuarios</td><td>CoordinacionController, InstructorAprendizController (trait CreaUsuarios).</td></tr>
    <tr><td class="clave">Llamados de atención</td><td>LlamadoController, InstructorLlamadoController.</td></tr>
    <tr class="alt"><td class="clave">Procesos disciplinarios</td><td>ProcesoController.</td></tr>
    <tr><td class="clave">Actas · Fichas · Programas</td><td>ActaController, FichaController, ProgramaController.</td></tr>
    <tr class="alt"><td class="clave">Reportes</td><td>CoordinacionReporteController, AprendizReporteController.</td></tr>
    <tr><td class="clave">Carga masiva</td><td>ImportacionController (ImportadorUsuarios, PlantillaImportacion).</td></tr>
    <tr class="alt"><td class="clave">Perfil · Firma · Manuales</td><td>PerfilController, ManualController.</td></tr>
    <tr><td class="clave">Notificaciones · Reglamento · Roles</td><td>NotificacionController, ReglamentoController, RolController.</td></tr>
</table>

{{-- ============================ 7. ROLES Y PERMISOS ============================ --}}
<h1 class="capitulo"><span class="num">7.</span> Roles y permisos</h1>
<p>
    El sistema define tres roles en <code>App\Support\Roles</code>: <b>Aprendiz</b>, <b>Instructor</b> y
    <b>Coordinador</b>. Un usuario puede tener varios roles; el rol activo se mantiene en sesión y se comparte
    a las vistas mediante el middleware <code>ShareActiveRole</code>.
</p>
<p>
    La restricción de rutas se aplica con el alias de middleware <code>rol</code>
    (<code>EnsureUserHasRole</code>), por ejemplo <code>->middleware('rol:Coordinador')</code>. Además, cada
    controlador valida en el backend el alcance de la operación (p. ej., un instructor solo gestiona
    aprendices de sus fichas; el manual técnico solo se descarga con rol activo Instructor o Coordinador).
</p>
<table class="datos">
    <tr><th>Recurso</th><th>Aprendiz</th><th>Instructor</th><th>Coordinador</th></tr>
    <tr><td class="clave">Ver sus llamados</td><td>Sí</td><td>Sí (de sus fichas)</td><td>Sí (todos)</td></tr>
    <tr class="alt"><td class="clave">Registrar llamado</td><td>No</td><td>Sí</td><td>Sí</td></tr>
    <tr><td class="clave">Gestionar usuarios</td><td>No</td><td>Solo aprendices de sus fichas</td><td>Sí (todos)</td></tr>
    <tr class="alt"><td class="clave">Reportes consolidados</td><td>No</td><td>Individuales de sus aprendices</td><td>Sí</td></tr>
    <tr><td class="clave">Manual de Usuario</td><td>Sí</td><td>Sí</td><td>Sí</td></tr>
    <tr class="alt"><td class="clave">Manual Técnico</td><td>No</td><td>Sí</td><td>Sí</td></tr>
</table>

{{-- ============================ 8. AUTENTICACIÓN Y SESIONES ============================ --}}
<h1 class="capitulo"><span class="num">8.</span> Autenticación y manejo de sesiones</h1>
<p>
    La autenticación usa el sistema nativo de Laravel adaptado al esquema propio: el modelo de usuario
    define <code>getAuthPasswordName() = 'password_hash'</code> y el acceso se realiza por correo. Las
    contraseñas se almacenan con hashing (bcrypt); nunca en texto plano.
</p>
<h2>Recordarme (remember me)</h2>
<p>
    Se implementa con el mecanismo nativo (<code>Auth::login($user, $remember)</code>) y la columna
    <code>remember_token</code>. La cookie es <b>HttpOnly</b> y no contiene la contraseña. Al cerrar sesión
    se invalida el token de recuerdo del dispositivo.
</p>
<h2>Seguridad de sesión</h2>
<p>El middleware <code>SeguridadSesion</code> refuerza el control de sesión:</p>
<ul>
    <li>Cabeceras <code>no-store</code> en páginas autenticadas: tras cerrar sesión, el botón «atrás» no muestra contenido cacheado.</li>
    <li>Cierre por cierre de pestaña, mediante una señal (<i>sendBeacon</i>) con periodo de gracia y señal de «sigo aquí» para evitar cierres falsos al navegar.</li>
    <li>El cierre automático por pestaña <b>no</b> se aplica a quienes marcaron «Recordarme» en ese dispositivo.</li>
</ul>

{{-- ============================ 9. SEGURIDAD ============================ --}}
<h1 class="capitulo"><span class="num">9.</span> Seguridad implementada</h1>
<table class="datos">
    <tr><th>Control</th><th>Descripción</th></tr>
    <tr><td class="clave">CSRF</td><td>Token en todos los formularios (protección nativa de Laravel).</td></tr>
    <tr class="alt"><td class="clave">Hashing</td><td>Contraseñas con bcrypt en <code>password_hash</code>.</td></tr>
    <tr><td class="clave">Autorización por rol</td><td>Middleware <code>rol</code> + validaciones de alcance en cada controlador.</td></tr>
    <tr class="alt"><td class="clave">Escapado de salida</td><td>Blade escapa por defecto; el resaltado del buscador escapa el HTML antes de insertar <code>&lt;mark&gt;</code>.</td></tr>
    <tr><td class="clave">Consultas seguras</td><td>Eloquent / query builder parametrizado (previene inyección SQL).</td></tr>
    <tr class="alt"><td class="clave">Archivos privados</td><td>Las firmas se guardan en disco privado (<code>storage/app/firmas</code>); solo el dueño accede.</td></tr>
    <tr><td class="clave">Transacciones</td><td>Operaciones críticas (altas, eliminaciones, carga masiva) en <code>DB::transaction</code> (todo o nada).</td></tr>
    <tr class="alt"><td class="clave">Trazabilidad</td><td>No se pueden eliminar usuarios con historial disciplinario; se conservan firmas e historiales.</td></tr>
</table>

{{-- ============================ 10. VALIDACIONES ============================ --}}
<h1 class="capitulo"><span class="num">10.</span> Validaciones</h1>
<p>Las validaciones se aplican en tres niveles coherentes entre sí:</p>
<ul>
    <li><b>Cliente (en vivo):</b> atributos <code>data-*</code> y utilidades de <code>validacion-vivo</code> que filtran y validan al escribir (solo letras, solo números, alfanumérico, correo, longitudes).</li>
    <li><b>Servidor (request):</b> reglas de Laravel en los controladores y en el trait <code>CreaUsuarios</code>.</li>
    <li><b>Carga masiva:</b> validación fila por fila en <code>ImportadorUsuarios</code> antes de importar.</li>
</ul>
<table class="datos">
    <tr><th>Campo</th><th>Regla</th></tr>
    <tr><td class="clave">Nombres / apellidos</td><td>Solo letras y espacios, 2–100 caracteres.</td></tr>
    <tr class="alt"><td class="clave">Documento (CC, TI, CE, PEP)</td><td>Solo números, 6–10 dígitos.</td></tr>
    <tr><td class="clave">Documento (PPT, Pasaporte)</td><td>Letras y números, 6–20 caracteres, sin espacios ni especiales.</td></tr>
    <tr class="alt"><td class="clave">Correo</td><td>Formato válido, único, hasta 120 caracteres.</td></tr>
    <tr><td class="clave">Teléfono</td><td>10 dígitos (opcional).</td></tr>
    <tr class="alt"><td class="clave">Contraseña</td><td>Mínimo 6 caracteres; hasheada al guardar.</td></tr>
</table>

{{-- ============================ 11. FLUJO ============================ --}}
<h1 class="capitulo"><span class="num">11.</span> Flujo del sistema</h1>
<p>Flujo típico de un llamado de atención desde su registro hasta el documento firmado:</p>
<table class="diagrama">
    <tr>
        <td>Instructor registra<br>el llamado</td>
        <td class="flecha">→</td>
        <td>Sistema notifica<br>al aprendiz (correo)</td>
        <td class="flecha">→</td>
        <td>Firmas<br>(instructor/coord./aprendiz)</td>
    </tr>
</table>
<table class="diagrama">
    <tr>
        <td class="neutro">Consulta y<br>seguimiento</td>
        <td class="flecha">→</td>
        <td class="neutro">Documento PDF<br>firmado</td>
        <td class="flecha">→</td>
        <td class="neutro">Proceso disciplinario<br>(si aplica)</td>
    </tr>
</table>
<div class="pie-diagrama">Figura 11.1 — Ciclo de vida de un llamado de atención</div>

{{-- ============================ 12. CARGA MASIVA ============================ --}}
<h1 class="capitulo"><span class="num">12.</span> Carga masiva de usuarios</h1>
<p>
    Implementada con <b>PhpSpreadsheet</b>. La clase <code>PlantillaImportacion</code> genera plantillas
    <code>.xlsx</code> con listas desplegables (validación de datos) y una hoja oculta <i>Listas</i> con un
    marcador <code>plantilla:{tipo}</code> que impide usar una plantilla en el rol equivocado. La clase
    <code>ImportadorUsuarios</code> realiza la lectura estricta (solo Xlsx/Xls), valida encabezados y cada
    fila, y ejecuta la importación en una transacción atómica (todo o nada), reutilizando el trait
    <code>CreaUsuarios</code>. Los errores se reportan por fila y las operaciones se auditan en un archivo
    de log (<code>storage/logs</code>), sin modificar la base de datos.
</p>

{{-- ============================ 13. FIRMAS ============================ --}}
<h1 class="capitulo"><span class="num">13.</span> Firmas digitales</h1>
<p>
    La firma manuscrita de cada usuario se almacena como imagen en un <b>disco privado</b>
    (<code>storage/app/firmas/firma_{id}.{ext}</code>). Al firmar un llamado, <code>App\Support\Firmas</code>
    incrusta la imagen (base64) en el documento PDF y registra la firma en la tabla <code>firma_llamado</code>
    con <code>id_llamado</code>, <code>id_usuario</code>, <code>rol_firma</code> y fecha. La restricción
    UNIQUE(id_llamado, rol_firma) evita firmas duplicadas por rol. El sistema exige tener firma registrada
    antes de permitir firmar.
</p>

{{-- ============================ 14. DOCUMENTOS Y REPORTES ============================ --}}
<h1 class="capitulo"><span class="num">14.</span> Generación de documentos y reportes</h1>
<ul>
    <li><b>Documento del llamado</b> (<code>DocumentoLlamado</code>): renderiza una vista Blade y la convierte a PDF con Dompdf; si la clase no existe, degrada a una vista imprimible.</li>
    <li><b>Reportes consolidados e individuales</b> (<code>CoordinacionReporteController</code>): exportan a PDF (vista imprimible), Excel (<code>ReporteExcel</code>, .xlsx real vía ZipArchive) y Word (.doc).</li>
    <li><b>Manuales</b> (<code>ManualPdf</code>): generan estos PDF con Dompdf, con portada, índice y pie de página numerado; descarga directa.</li>
</ul>
<div class="caja caja-info">
    <b>Requisito de Dompdf:</b> necesita la extensión <code>gd</code> de PHP habilitada en <code>php.ini</code>
    para procesar imágenes. Tras habilitarla se debe reiniciar Apache.
</div>

{{-- ============================ 15. CORREO ============================ --}}
<h1 class="capitulo"><span class="num">15.</span> Notificaciones por correo</h1>
<p>
    El envío de correo usa <b>PHPMailer</b> por SMTP directo (clases <code>CorreoLlamado</code> y
    <code>CorreoRecuperacion</code>). Se configura mediante variables en <code>.env</code> (host, puerto,
    usuario, contraseña y cifrado). Existe un modo de prueba <b>«log»</b> que escribe el correo en el log en
    lugar de enviarlo, útil para pruebas sin servidor SMTP.
</p>
<div class="caja caja-alerta">
    <b>Antivirus y TLS:</b> algunos antivirus interceptan TLS y pueden impedir el envío. En ese caso se
    ajusta el parámetro de verificación TLS en <code>.env</code>. Verifique siempre que las credenciales SMTP
    y el remitente estén correctos.
</div>

{{-- ============================ 16. DESPLIEGUE ============================ --}}
<h1 class="capitulo"><span class="num">16.</span> Base de datos, descarga e implementación</h1>
<p>
    Este capítulo explica, de forma detallada, cómo cualquier usuario técnico puede descargar, instalar y poner en
    funcionamiento GEVLA en su propio equipo, desde cero. Está pensado para el entorno de desarrollo/pruebas sobre
    <b>Windows con XAMPP</b>, que es el usado en el proyecto.
</p>

<h2>16.1 Descarga del proyecto y la base de datos</h2>
<p>
    El código fuente completo del sistema (comprimido en un archivo <b>.ZIP</b>) y el respaldo de la base de datos
    <code>sena_disciplinario</code> (archivo <b>.SQL</b>) se encuentran alojados en la siguiente carpeta de Google Drive:
</p>
<div class="caja caja-info">
    <b>Repositorio del proyecto y base de datos (Google Drive):</b><br>
    <a href="https://drive.google.com/drive/folders/1LindWmmy5B7vwRIFN1C5-bEzptZ8fdfG?usp=sharing" style="color:#00324d; word-break:break-all;">https://drive.google.com/drive/folders/1LindWmmy5B7vwRIFN1C5-bEzptZ8fdfG</a><br>
    <span style="color:#55655c;">Contiene: el ZIP con todo el proyecto (código fuente + dependencias) y el archivo SQL de la base de datos.</span>
</div>
<p>
    Se recomienda descargar <b>ambos archivos</b> (el ZIP del proyecto y el .SQL de la base de datos) antes de empezar,
    y guardarlos en una carpeta temporal de fácil acceso (por ejemplo, la carpeta <b>Descargas</b>).
</p>

<h2>16.2 Requisitos previos (software a instalar)</h2>
<p>Antes de desplegar el sistema, cada usuario debe tener instalado el siguiente software en su equipo:</p>
<table class="datos">
    <tr><th>Software</th><th>Para qué sirve</th><th>Dónde se obtiene</th></tr>
    <tr><td class="clave">XAMPP</td><td>Paquete que incluye Apache, MariaDB/MySQL, PHP y phpMyAdmin. Es el corazón del entorno.</td><td>apachefriends.org (versión con PHP 8.2 o superior).</td></tr>
    <tr class="alt"><td class="clave">Composer</td><td>Gestor de dependencias de PHP (instala Laravel, Dompdf, PhpSpreadsheet, etc.).</td><td>getcomposer.org</td></tr>
    <tr><td class="clave">Node.js + npm</td><td>Compila los estilos (Tailwind) y los assets del frontend con Vite.</td><td>nodejs.org (versión LTS).</td></tr>
    <tr class="alt"><td class="clave">Navegador web</td><td>Para usar el sistema (Chrome, Edge o Firefox actualizados).</td><td>Preinstalado en el sistema operativo.</td></tr>
</table>
<div class="caja">
    <b>Nota:</b> con <b>XAMPP</b> ya quedan cubiertos el servidor web (Apache), el gestor de base de datos
    (MariaDB/MySQL + phpMyAdmin) y PHP. Composer y Node.js solo son necesarios si se instala desde el código fuente
    (recomendado); si se usa el ZIP con las dependencias ya incluidas, Composer puede no ser indispensable.
</div>

<h2>16.3 Requisitos de la base de datos</h2>
<table class="datos">
    <tr><th>Requisito</th><th>Descripción</th></tr>
    <tr><td class="clave">Gestor de base de datos</td><td>Motor <b>MariaDB o MySQL</b> con un administrador como <b>phpMyAdmin</b> (ambos incluidos en XAMPP) para crear la base de datos e importar el archivo SQL.</td></tr>
    <tr class="alt"><td class="clave">PHP</td><td>Intérprete <b>PHP 8.2 o superior</b>, con la extensión <code>gd</code> habilitada (necesaria para que Dompdf genere los PDF con imágenes).</td></tr>
    <tr><td class="clave">Nombre de la base</td><td><code>sena_disciplinario</code> (debe coincidir con el valor de <code>DB_DATABASE</code> del archivo <code>.env</code>).</td></tr>
    <tr class="alt"><td class="clave">Motor de tablas</td><td><b>InnoDB</b> (para respetar las claves foráneas y la integridad referencial del esquema).</td></tr>
</table>

<h2>16.4 Instalación paso a paso</h2>

<h3>A. Preparar el entorno</h3>
<table class="pasos">
    <tr><td class="paso">1</td><td>Instalar <b>XAMPP</b>. Abrir el <b>Panel de control de XAMPP</b> e iniciar los módulos <b>Apache</b> y <b>MySQL</b> (deben quedar en verde).</td></tr>
    <tr><td class="paso">2</td><td>Descargar de Drive el <b>ZIP del proyecto</b> y descomprimirlo dentro de <code>C:\xampp\htdocs\GEVLA</code>.</td></tr>
</table>

<h3>B. Montar la base de datos</h3>
<table class="pasos">
    <tr><td class="paso">3</td><td>Abrir <b>phpMyAdmin</b> en el navegador: <code>http://localhost/phpmyadmin</code>.</td></tr>
    <tr><td class="paso">4</td><td>Crear una base de datos nueva llamada <b>exactamente</b> <code>sena_disciplinario</code> (cotejamiento <code>utf8mb4_unicode_ci</code>).</td></tr>
    <tr><td class="paso">5</td><td>Con esa base seleccionada, ir a la pestaña <b>Importar</b>, elegir el archivo <b>.SQL</b> descargado de Drive y presionar <b>Continuar</b>. Al terminar, deben aparecer todas las tablas del esquema.</td></tr>
</table>

<h3>C. Configurar el proyecto</h3>
<table class="pasos">
    <tr><td class="paso">6</td><td>En la carpeta del proyecto, copiar el archivo <code>.env.example</code> y renombrar la copia como <code>.env</code>.</td></tr>
    <tr><td class="paso">7</td><td>Editar el <code>.env</code> con los datos de conexión a la base de datos y del correo (ver <b>16.5</b>).</td></tr>
    <tr><td class="paso">8</td><td>Abrir una terminal en la carpeta del proyecto y ejecutar <code>composer install</code> (instala Dompdf, PhpSpreadsheet y demás dependencias).</td></tr>
    <tr><td class="paso">9</td><td>Ejecutar <code>php artisan key:generate</code> para generar la clave de cifrado de la aplicación.</td></tr>
    <tr><td class="paso">10</td><td>Ejecutar <code>npm install</code> y luego <code>npm run build</code> para compilar los estilos (Tailwind) y los assets.</td></tr>
    <tr><td class="paso">11</td><td>Habilitar la extensión <code>gd</code>: en <code>C:\xampp\php\php.ini</code> quitar el punto y coma de la línea <code>;extension=gd</code> (dejarla como <code>extension=gd</code>) y <b>reiniciar Apache</b>.</td></tr>
</table>

<h3>D. Ejecutar el sistema</h3>
<table class="pasos">
    <tr><td class="paso">12</td><td>Abrir el sistema en el navegador. Dos opciones: con Apache, entrar a <code>http://localhost/GEVLA/public</code>; o con el servidor de Laravel, ejecutar <code>php artisan serve</code> y entrar a <code>http://127.0.0.1:8000</code>.</td></tr>
    <tr><td class="paso">13</td><td>Iniciar sesión con un usuario registrado en la base de datos (correo y contraseña). La contraseña inicial de las cuentas creadas es el número de documento.</td></tr>
</table>

<h2>16.5 Configuración del archivo .env</h2>
<p>El archivo <code>.env</code> contiene la configuración del entorno. Los bloques más importantes son la conexión a la base de datos y el correo:</p>
<div class="bloque-codigo">
# Base de datos<br>
DB_CONNECTION=mysql<br>
DB_HOST=127.0.0.1<br>
DB_PORT=3306<br>
DB_DATABASE=sena_disciplinario<br>
DB_USERNAME=root<br>
DB_PASSWORD=<br>
<br>
# Correo (PHPMailer / SMTP)<br>
MAIL_HOST=smtp.gmail.com<br>
MAIL_PORT=587<br>
MAIL_USERNAME=tu_correo@gmail.com<br>
MAIL_PASSWORD=clave_de_aplicacion<br>
MAIL_FROM_ADDRESS=tu_correo@gmail.com<br>
MAIL_VERIFICAR_TLS=false
</div>
<table class="datos">
    <tr><th>Variable</th><th>Qué poner</th></tr>
    <tr><td class="clave">DB_DATABASE</td><td>El nombre de la base creada: <code>sena_disciplinario</code>.</td></tr>
    <tr class="alt"><td class="clave">DB_USERNAME / DB_PASSWORD</td><td>Usuario y contraseña de MySQL. En XAMPP por defecto es <code>root</code> sin contraseña.</td></tr>
    <tr><td class="clave">MAIL_*</td><td>Datos del correo que envía las notificaciones. Con Gmail se usa una <b>contraseña de aplicación</b>, no la clave normal.</td></tr>
    <tr class="alt"><td class="clave">MAIL_VERIFICAR_TLS</td><td>En <code>false</code> si un antivirus intercepta el TLS e impide el envío; existe también un modo de prueba que escribe el correo en el log.</td></tr>
</table>

<h2>16.6 Verificación de la instalación</h2>
<p>Para confirmar que todo quedó bien montado, revise:</p>
<ul>
    <li>En el panel de XAMPP, <b>Apache</b> y <b>MySQL</b> están iniciados (en verde).</li>
    <li>En phpMyAdmin, la base <code>sena_disciplinario</code> muestra todas sus tablas con datos.</li>
    <li>La página de <b>inicio de sesión</b> de GEVLA carga con estilos (si se ve sin diseño, faltó <code>npm run build</code>).</li>
    <li>Se puede <b>iniciar sesión</b> y ver el panel del rol.</li>
    <li>Al descargar un <b>documento PDF</b> o un <b>reporte</b>, el archivo se genera correctamente (confirma que <code>gd</code> está habilitada).</li>
</ul>

<h2>16.7 Solución de problemas comunes</h2>
<table class="datos">
    <tr><th>Problema</th><th>Causa y solución</th></tr>
    <tr><td class="clave">La página se ve sin estilos</td><td>No se compilaron los assets. Ejecutar <code>npm install</code> y <code>npm run build</code>, y recargar con Ctrl+F5.</td></tr>
    <tr class="alt"><td class="clave">"The PHP GD extension is required"</td><td>Falta habilitar <code>gd</code>. Editar <code>php.ini</code> (<code>extension=gd</code>) y reiniciar Apache.</td></tr>
    <tr><td class="clave">"could not find driver" / error de conexión a BD</td><td>Habilitar <code>extension=pdo_mysql</code> en <code>php.ini</code> y verificar los datos de <code>DB_*</code> en el <code>.env</code>.</td></tr>
    <tr class="alt"><td class="clave">Composer: "curl error 60" (SSL)</td><td>Un antivirus intercepta el certificado. Configurar el <code>cafile</code> de Composer con los certificados del sistema.</td></tr>
    <tr><td class="clave">MySQL no inicia en XAMPP</td><td>Puerto 3306 ocupado o datos InnoDB corruptos. Liberar el puerto o restaurar el <i>data dir</i> desde un respaldo.</td></tr>
    <tr class="alt"><td class="clave">No llegan los correos</td><td>Revisar credenciales SMTP, usar contraseña de aplicación y ajustar <code>MAIL_VERIFICAR_TLS=false</code>; o usar el modo de prueba (log).</td></tr>
    <tr><td class="clave">"No application encryption key"</td><td>No se generó la clave. Ejecutar <code>php artisan key:generate</code>.</td></tr>
    <tr class="alt"><td class="clave">Cambios de vistas no se reflejan</td><td>Limpiar caché: <code>php artisan optimize:clear</code> (o <code>view:clear</code>).</td></tr>
</table>

<div class="caja caja-alerta">
    <b>Importante:</b> el archivo <b>.SQL</b> de Drive ya incluye todas las tablas del esquema (equivalente a los
    módulos SQL de <code>database/sql</code>), por lo que al importarlo la base queda lista para usarse. Tras cada
    actualización del proyecto, vuelva a ejecutar <code>composer install</code>, <code>npm run build</code> e importe
    la versión más reciente de la base de datos desde Drive.
</div>

{{-- ============================ 17. MANTENIMIENTO ============================ --}}
<h1 class="capitulo"><span class="num">17.</span> Mantenimiento</h1>
<table class="datos">
    <tr><th>Tarea</th><th>Recomendación</th></tr>
    <tr><td class="clave">Respaldo de BD</td><td>Copias periódicas con <code>mysqldump</code>; conservar respaldos antes de cambios de esquema.</td></tr>
    <tr class="alt"><td class="clave">Caché de vistas</td><td>Tras cambios en Blade: <code>php artisan view:clear</code> y opcionalmente <code>view:cache</code>.</td></tr>
    <tr><td class="clave">Logs</td><td>Revisar <code>storage/logs</code> (aplicación, importaciones y correo en modo log).</td></tr>
    <tr class="alt"><td class="clave">Assets</td><td>Recompilar con <code>npm run build</code> cuando se agreguen clases nuevas de Tailwind.</td></tr>
    <tr><td class="clave">Recuperación InnoDB</td><td>Ante corrupción: respaldar el data dir, iniciar con <code>--innodb-force-recovery</code>, exportar y reconstruir.</td></tr>
</table>

{{-- ============================ 18. ACTUALIZACIONES ============================ --}}
<h1 class="capitulo"><span class="num">18.</span> Procedimientos para futuras actualizaciones</h1>
<table class="pasos">
    <tr><td class="paso">1</td><td>Trabajar en una rama aparte y respaldar la base de datos antes de aplicar cambios.</td></tr>
    <tr><td class="paso">2</td><td>Traer los cambios (<code>git pull</code>) y resolver conflictos si los hubiera.</td></tr>
    <tr><td class="paso">3</td><td>Ejecutar <code>composer install</code> y <code>npm run build</code>.</td></tr>
    <tr><td class="paso">4</td><td>Importar los nuevos módulos SQL (<code>database/sql/modulo_*.sql</code>), que son idempotentes.</td></tr>
    <tr><td class="paso">5</td><td>Limpiar cachés (<code>php artisan optimize:clear</code>) y verificar el funcionamiento.</td></tr>
    <tr><td class="paso">6</td><td>Nuevas funcionalidades incrementales: seguir el patrón de módulo SQL + degradación con <code>Schema::hasTable</code>.</td></tr>
</table>

{{-- ============================ 19. RECOMENDACIONES ============================ --}}
<h1 class="capitulo"><span class="num">19.</span> Recomendaciones</h1>
<ul>
    <li>Mantener las dependencias actualizadas dentro de las versiones compatibles.</li>
    <li>No exponer <code>.env</code> ni credenciales; usar cuentas SMTP y de BD con privilegios mínimos.</li>
    <li>Conservar el patrón de traits y clases de soporte para evitar duplicación entre roles.</li>
    <li>Documentar cada nuevo módulo SQL y cada cambio de esquema.</li>
    <li>Respetar las validaciones en los tres niveles (cliente, servidor, carga masiva) al agregar campos.</li>
    <li>Preservar la trazabilidad: nunca eliminar registros con historial disciplinario.</li>
</ul>
<div class="caja">
    <b>GEVLA</b> — Manual Técnico · Servicio Nacional de Aprendizaje — SENA.<br>
    Documento de uso interno para el personal de soporte técnico del centro de formación.
</div>
</body>
</html>
