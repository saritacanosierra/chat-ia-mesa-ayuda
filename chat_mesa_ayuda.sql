-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-11-2025 a las 22:07:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `chat_mesa_ayuda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `app_config`
--

CREATE TABLE `app_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `app_config`
--

INSERT INTO `app_config` (`id`, `config_key`, `config_value`, `description`, `updated_at`, `created_at`) VALUES
(1, 'admin_password', 'quokka123456', 'Contraseña de administrador para acceder a la configuración', '2025-11-05 17:51:52', '2025-11-05 22:51:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `files`
--

CREATE TABLE `files` (
  `id` varchar(50) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `chunks` int(11) NOT NULL,
  `size` int(11) DEFAULT 0,
  `uploaded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `files`
--

INSERT INTO `files` (`id`, `filename`, `type`, `chunks`, `size`, `uploaded_at`, `created_at`) VALUES
('file_690bc872a53e1', 'Diagnostico .xlsx', 'xlsx', 58, 9111, '2025-11-05 22:58:10', '2025-11-05 21:58:10'),
('file_690bcdc4262ca', 'Preguntas_Frecuentes_Mesa_de_Servicio_CallCenter.pdf', 'pdf', 301, 4453, '2025-11-05 23:20:52', '2025-11-05 22:20:52'),
('file_690cb67bb6daa', 'Pantallas - Monitores.pdf', 'pdf', 301, 137109, '2025-11-06 15:53:47', '2025-11-06 14:53:47'),
('file_690cbba0d0ea9', 'Impresoras.pdf', 'pdf', 301, 166679, '2025-11-06 16:15:44', '2025-11-06 15:15:44'),
('file_690cbe2d0c454', 'Diademas Inalámbricas.pdf', 'pdf', 301, 161576, '2025-11-06 16:26:37', '2025-11-06 15:26:37'),
('file_690cbf7350c4e', 'Teclados.pdf', 'pdf', 301, 149006, '2025-11-06 16:32:03', '2025-11-06 15:32:03'),
('file_690cc21aa1fb2', 'Mouse.pdf', 'pdf', 301, 147885, '2025-11-06 16:43:22', '2025-11-06 15:43:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `frequent_questions`
--

CREATE TABLE `frequent_questions` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `source` text DEFAULT NULL,
  `times_asked` int(11) DEFAULT 1,
  `last_asked_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `frequent_questions`
--

INSERT INTO `frequent_questions` (`id`, `question`, `answer`, `source`, `times_asked`, `last_asked_at`, `created_at`) VALUES
(1, 'sobre que conoces?', '¡Hola! Con gusto te cuento sobre lo que tengo información:\n\nParece que tengo datos sobre tickets de soporte de facturación. Puedo ver información como:\n\n*   **Estado del ticket:** Abierto, Cerrado, En proceso.\n*   **Prioridad:** Alta, Media, Baja.\n*   **Número de ticket:** (Ejemplo: TKT-1046).\n*   **Fecha y hora de creación del ticket.**\n*   **Agente asignado:** (Ejemplo: Andrés, Carlos, Laura).\n*   **Canal de comunicación:** Correo, Chat, Teléfono.\n*   **Tipo de problema:** Consulta general, Problema técnico, Reclamo.\n*   **Tiempo de resolución.**\n\nBásicamente, puedo ayudarte a encontrar información sobre tickets específicos, su estado, quién los está atendiendo y de qué se tratan. ¿Hay algo en particular sobre lo que te gustaría saber?\n', 'Fragmento 120 de 690bc1217dd50.xlsx, Fragmento 120 de 690bc19c5a100.xlsx', 1, '2025-11-05 16:56:59', '2025-11-05 21:56:59'),
(2, 'no tengo audio en mi diadema', '¡Hola! Entiendo que no tienes audio en tu diadema. Según la información que tengo, esto podría estar relacionado con la configuración inicial de audio/micrófono. Te recomiendo que solicites una configuración inicial de audio/micrófono para tu diadema. Si el problema persiste, ¡no dudes en contactarme de nuevo! 😊\n', 'Fragmento 20 de 690bc847efcd9.xlsx, Fragmento 21 de 690bc847efcd9.xlsx', 1, '2025-11-05 17:00:18', '2025-11-05 22:00:18'),
(3, 'mi red no funciona', '¡Hola! Lamento que estés teniendo problemas con tu red. Aquí hay algunas cosas que puedes intentar para solucionar el problema:\n\n1.  **Comprueba si otros dispositivos tienen el mismo problema.** Esto te ayudará a determinar si el problema es con tu red en general o solo con tu dispositivo.\n2.  **Reinicia el router.** A veces, simplemente reiniciar el router puede solucionar problemas de conexión.\n3.  **Conéctate por cable directo.** Si normalmente te conectas a través de Wi-Fi, intenta conectarte directamente al router con un cable Ethernet. Esto puede ayudarte a determinar si el problema es con tu conexión inalámbrica.\n4.  **Si el sistema muestra \'sin conexión al servidor\',** verifica tu VPN o red corporativa. Si el servidor está en mantenimiento, espera el restablecimiento.\n5.  **Si el problema persiste y se cae la llamada constantemente**, podría deberse a una red inestable. Cierra otras aplicaciones que usen internet y contacta al área de infraestructura.\n\nSi después de intentar estos pasos sigues teniendo problemas, no dudes en contactar al coordinador de mesa de servicio o registrar un ticket con prioridad alta para que te puedan ayudar de manera más específica. ¡Espero que esto te ayude!\n', 'Fragmento 40 de 690bcd08ad83f.pdf, Fragmento 6 de 690bcd08ad83f.pdf', 1, '2025-11-05 17:24:04', '2025-11-05 22:24:04'),
(4, 'tengo problemas con mi red', '¡Hola! Lamento que estés teniendo problemas con tu red. Aquí hay algunos pasos que puedes seguir para intentar solucionar el problema:\n\n<ul>\n  <li>Si estás usando Wi-Fi, intenta reiniciar tu router o conecta tu dispositivo directamente por cable.</li>\n  <li>Si las llamadas se caen constantemente, cierra otras aplicaciones que estén usando internet. Si el problema persiste, contacta al área de infraestructura.</li>\n  <li>Si el sistema muestra el mensaje \"sin conexión al servidor\", verifica tu conexión VPN o la red corporativa. También es posible que el servidor esté en mantenimiento, en cuyo caso, solo tendrás que esperar a que se restablezca.</li>\n</ul>\n\nEspero que esto te ayude a solucionar tus problemas de red. Si el problema persiste, no dudes en contactar al área de infraestructura para obtener ayuda adicional. ¡Mucha suerte!\n', 'Fragmento 40 de 690bcd08ad83f.pdf, Fragmento 39 de 690bcd08ad83f.pdf', 2, '2025-11-05 17:26:10', '2025-11-05 22:25:43'),
(5, 'mi mouse no funciona', '¡Hola! Lamento que estés teniendo problemas con tu mouse. Entiendo que ya has probado algunos pasos y aún no funciona.\n\nSegún la información que tengo, estos son algunos escenarios en los que probablemente necesites reemplazar el mouse:\n\n<ul>\n <li>Si hay daño físico visible, como un cable roto, la carcasa abierta o una batería inflada.</li>\n <li>Si el equipo no lo reconoce en ningún puerto USB.</li>\n <li>Si el mouse presenta movimientos erráticos constantes o clics involuntarios incluso después de limpiarlo y reconectarlo.</li>\n</ul>\n\nSi te encuentras en alguna de estas situaciones, lo más probable es que sea hora de conseguir un nuevo mouse. ¡Espero que esto te ayude!\n', 'Fragmento 252 de 690cc149607df.pdf, Fragmento 254 de 690cc149607df.pdf', 1, '2025-11-06 10:44:22', '2025-11-06 15:44:22'),
(6, 'no no tiene esos daños', '¡Entendido! Si tu dispositivo no presenta daño físico ni batería inflada, entonces podemos explorar otras opciones para ayudarte. ¿Podrías describir el problema que estás experimentando? Con más detalles, podré ofrecerte una solución más precisa.\n', 'Fragmento 53 de 690bc847efcd9.xlsx, Fragmento 54 de 690bc847efcd9.xlsx', 1, '2025-11-06 10:44:54', '2025-11-06 15:44:54'),
(7, 'mi mouse es inalambrico y no prende la luz de abajo', '¡Hola! Entiendo que tienes un problema con tu mouse inalámbrico que no enciende la luz de abajo. Aquí te dejo algunas cosas que puedes revisar para intentar solucionar el problema:\n\n<ul>\n  <li>**Baterías:** Si es inalámbrico, lo primero es revisar o cambiar las baterías. A veces, aunque parezcan tener carga, pueden estar fallando.</li>\n  <li>**Conexión:** Asegúrate de que el receptor USB esté bien conectado al equipo. Prueba también en otro puerto USB.</li>\n  <li>**Encendido:** Verifica que el mouse esté encendido.</li>\n  <li>**Proximidad:** Acerca el mouse al receptor, evitando que haya obstáculos entre ellos.</li>\n  <li>**Prueba en otro equipo:** Conecta el mouse a otro equipo para confirmar si funciona. Esto ayuda a descartar si el problema es del mouse o de tu computadora.</li>\n</ul>\n\nSi después de probar estos pasos el mouse sigue sin funcionar, o si ves algún daño físico (como un cable roto, la carcasa abierta o la batería inflada), o si ningún equipo lo reconoce, entonces lo mejor será que reportes la incidencia a soporte técnico para que te puedan ayudar de manera más específica. ¡Espero que esto te ayude!\n', 'Fragmento 4 de 690cc149607df.pdf, Fragmento 252 de 690cc149607df.pdf', 1, '2025-11-06 10:45:19', '2025-11-06 15:45:19'),
(8, 'sabes de teclados', '¡Hola! 👋 Sí, tengo información sobre teclados. Aquí te dejo algunos consejos y soluciones a problemas comunes que podrías encontrar:\n\nSi tu teclado tiene teclas pegadas o no responde bien:\n<ul>\n  <li>Apaga o desconecta el teclado antes de limpiarlo.</li>\n  <li>Usa aire comprimido o un paño seco para quitar el polvo y los residuos.</li>\n  <li>Si alguna tecla está atascada, presiónala suavemente varias veces.</li>\n  <li>Evita usar líquidos o aerosoles directamente sobre el teclado.</li>\n</ul>\n\nSi las funciones especiales de tu teclado no responden:\n<ul>\n  <li>Verifica que los drivers del teclado estén instalados correctamente.</li>\n  <li>Reinicia el equipo.</li>\n  <li>Si el teclado tiene software propio (ej. Logitech, HP, Dell), abre el programa y revisa la configuración.</li>\n  <li>Si sigue sin funcionar, solicita asistencia al área de soporte.</li>\n</ul>\n\n¿Cuándo debes reportar a soporte técnico?\n<ul>\n  <li>Si el teclado no responde después de probar los pasos anteriores.</li>\n  <li>Si presenta daño físico visible (teclas sueltas, cable roto, batería inflada).</li>\n  <li>Si ningún puerto USB lo reconoce.</li>\n  <li>Si el equipo no detecta el dispositivo tras la reconexión.</li>\n</ul>\n\nEspero que esto te sea útil. ¡Si tienes alguna otra pregunta, no dudes en consultarme! 😊\n', 'Fragmento 299 de 690cbec5e717d.pdf, Fragmento 149 de 690cbec5e717d.pdf', 1, '2025-11-06 11:19:19', '2025-11-06 16:19:19'),
(9, 'no funciona mi mouse', '¡Hola! Lamento escuchar que tienes problemas con tu mouse. Entiendo que ya probaste algunos pasos y aún no funciona.\n\nSegún la información que tengo, estos son algunos escenarios en los que definitivamente deberías considerar que el mouse tiene un problema mayor:\n\n<ul>\n  <li>Si hay daño físico visible (cable roto, carcasa abierta, batería inflada).</li>\n  <li>Si no lo reconoce ningún equipo o puerto USB.</li>\n  <li>Si presenta movimiento errático constante o clics involuntarios tras limpieza y reconexión.</li>\n</ul>\n\nSi alguna de estas situaciones aplica a tu caso, es probable que el mouse necesite ser reemplazado. ¡Espero que esta información te sea útil!\n', 'Fragmento 252 de 690cc149607df.pdf, Fragmento 254 de 690cc149607df.pdf', 1, '2025-11-06 11:43:16', '2025-11-06 16:43:16'),
(10, 'se me daño el mouse', '¡Hola! Lamento escuchar que tu mouse se dañó. Para entender mejor la situación, ¿podrías decirme qué tipo de daño presenta?\n\nSegún la información que tengo, estos son algunos escenarios donde se considera que un mouse necesita atención:\n\n<ul>\n <li>Si hay daño físico visible, como un cable roto, la carcasa abierta o la batería inflada.</li>\n <li>Si la computadora no lo reconoce, incluso al probarlo en diferentes puertos USB.</li>\n <li>Si el cursor tiene movimientos erráticos o hace clics sin que lo estés usando, incluso después de limpiarlo y reconectarlo.</li>\n</ul>\n\nSi tu mouse presenta alguna de estas fallas, o si ya intentaste solucionarlo sin éxito, podría ser momento de considerar otras opciones. ¡Avísame si necesitas más ayuda!\n', 'Fragmento 264 de 690cc149607df.pdf, Fragmento 263 de 690cc149607df.pdf', 1, '2025-11-06 11:47:45', '2025-11-06 16:47:45'),
(11, 'es inalambrico no tiene cable', '¡Hola! Entiendo que tu dispositivo inalámbrico no funciona correctamente. Para ayudarte mejor, necesito saber qué tipo de dispositivo es. ¿Es un teclado o una diadema inalámbrica?\n\nMientras tanto, aquí hay algunos consejos generales que pueden ayudarte a solucionar problemas de conexión en dispositivos inalámbricos:\n\n*   **Verifica la conexión:**\n    *   Asegúrate de que el receptor USB esté bien conectado a tu computadora.\n    *   Enciende el dispositivo y verifica que el indicador de batería esté activo (si aplica).\n\n*   **Batería:**\n    *   Reemplaza las baterías si son reemplazables o recarga el dispositivo si es recargable.\n\n*   **Acercamiento:**\n    *   Acerca el dispositivo al receptor USB, asegurándote de que no haya obstáculos entre ellos.\n\n*   **Prueba en otro puerto/equipo:**\n    *   Si no conecta, intenta conectarlo a otro puerto USB o a otra computadora.\n\nSi después de intentar estos pasos sigues teniendo problemas, o si tu dispositivo presenta daño físico, no dudes en contactar al soporte técnico para obtener ayuda adicional.', 'Fragmento 299 de 690cbd4f49c6f.pdf, Fragmento 79 de 690cbec5e717d.pdf', 1, '2025-11-06 11:48:02', '2025-11-06 16:48:02'),
(12, 'se daño mi mouse', '¡Hola! Lamento escuchar que tu mouse se ha dañado. Para entender mejor la situación, ¿podrías indicarme qué tipo de daño presenta?\n\nSegún la información que tengo, estos son algunos escenarios en los que se considera que un mouse está dañado y necesita atención:\n\n<ul>\n  <li>Si hay daño físico visible (cable roto, carcasa abierta, batería inflada).</li>\n  <li>Si no lo reconoce ningún equipo o puerto USB.</li>\n  <li>Si presenta movimiento errático constante o clics involuntarios tras limpieza y reconexión.</li>\n</ul>\n\nDependiendo de la naturaleza del daño, te puedo dar algunas recomendaciones o pasos a seguir. ¡Espero tu respuesta!\n', 'Fragmento 264 de 690cc149607df.pdf, Fragmento 263 de 690cc149607df.pdf', 1, '2025-11-06 11:51:18', '2025-11-06 16:51:18'),
(13, 'seme daño el mouse', '¡Hola! Lamento escuchar que tu mouse se dañó. Para entender mejor la situación, ¿podrías decirme qué tipo de problema presenta?\n\nSegún la información que tengo, estos son algunos escenarios donde podría ser necesaria una revisión o reemplazo:\n\n<ul>\n <li>Si hay daño físico visible (cable roto, carcasa abierta, batería inflada).</li>\n <li>Si no lo reconoce ningún equipo o puerto USB.</li>\n <li>Si presenta movimiento errático constante o clics involuntarios, incluso después de limpiarlo y reconectarlo.</li>\n</ul>\n\nSi tu problema coincide con alguna de estas situaciones, o si tienes otros detalles que puedan ayudarme a entender mejor, ¡no dudes en contármelos! Así podré darte una mejor orientación.\n', 'Fragmento 264 de 690cc149607df.pdf, Fragmento 263 de 690cc149607df.pdf', 1, '2025-11-06 11:56:57', '2025-11-06 16:56:57'),
(14, 'no escriben los numeros asi los marque en el teclado', 'Entiendo, si los números no se escriben correctamente al marcarlos en el teclado, esto puede deberse a varias razones. Aquí te dejo algunas cosas que puedes revisar:\n\n<ul>\n  <li><b>Verifica el idioma del teclado:</b> Asegúrate de que esté configurado en Español (Latinoamérica) o Español (España) en las opciones de idioma de Windows. Puedes encontrar esta configuración en: Configuración → Hora e idioma → Idioma → Teclado. Elimina cualquier idioma que no estés utilizando.</li>\n  <li><b>Revisa si está activado el Bloq Num:</b> La función Bloq Num (o Num Lock) debe estar activada para que los números del teclado numérico funcionen. Busca la tecla \"Bloq Num\" o \"Num Lock\" en tu teclado y asegúrate de que la luz indicadora esté encendida.</li>\n  <li><b>Teclas pegadas:</b> Si alguna tecla está atascada, presiónala suavemente varias veces para asegurarte de que no esté bloqueada mecánicamente.</li>\n</ul>\n\nSi después de revisar esto el problema persiste, podría ser algo más complejo y tal vez necesites contactar al soporte técnico.\n', 'Fragmento 17 de 690cbec5e717d.pdf, Fragmento 16 de 690cbec5e717d.pdf', 1, '2025-11-06 12:20:17', '2025-11-06 17:20:17'),
(15, 'y mi mouse inalambrico no funciona', 'Entiendo, tu mouse inalámbrico no funciona. Aquí hay algunas cosas que puedes intentar para solucionar el problema:\n\n<ul>\n <li>Verifica que el receptor USB esté conectado al equipo.</li>\n <li>Acerca el mouse al receptor (sin obstáculos).</li>\n <li>Asegúrate de que el mouse esté encendido y que tenga baterías nuevas o cargadas.</li>\n <li>Prueba en otro puerto USB.</li>\n <li>Si el mouse no se conecta, prueba en otro equipo.</li>\n <li>Limpia la parte inferior del mouse (sensor óptico) con un paño seco.</li>\n <li>Asegúrate de usar el mouse sobre una superficie adecuada (no brillante o de vidrio).</li>\n <li>Prueba en otra superficie o con una alfombrilla.</li>\n <li>Desconecta y vuelve a conectar el mouse.</li>\n</ul>\n\nSi después de intentar estos pasos el mouse sigue sin funcionar, podría ser momento de reportarlo a soporte técnico, especialmente si:\n\n<ul>\n <li>El mouse no responde tras probar los pasos anteriores.</li>\n <li>Hay daño físico visible (cable roto, carcasa abierta, batería inflada).</li>\n <li>No lo reconoce ningún equipo o puerto USB.</li>\n <li>Presenta movimiento errático constante o clics involuntarios tras limpieza y reconexión.</li>\n</ul>\n', 'Fragmento 93 de 690cc149607df.pdf, Fragmento 92 de 690cc149607df.pdf', 1, '2025-11-06 12:20:49', '2025-11-06 17:20:49'),
(16, 'neceito ayuda con mi teclado', '¡Claro! Para ayudarte con tu teclado, necesito saber qué problema estás experimentando. Aquí hay algunas situaciones comunes y sus posibles soluciones:\n\n1.  **Si tu teclado no responde:**\n    *   Verifica la conexión física (cable USB o receptor inalámbrico).\n    *   Si es inalámbrico, revisa la batería y cámbiala o recárgala si es necesario.\n    *   Prueba en otro puerto USB o en otro equipo para descartar problemas de compatibilidad.\n\n2.  **Si tienes teclas pegadas o que no responden bien:**\n    *   Apaga o desconecta el teclado antes de limpiarlo.\n    *   Usa aire comprimido o un paño seco para quitar el polvo y los residuos.\n    *   Si una tecla está atascada, presiónala suavemente varias veces.\n    *   Evita usar líquidos o aerosoles directamente sobre el teclado.\n\n3.  **Si las funciones especiales de tu teclado no responden:**\n    *   Verifica que los drivers del teclado estén instalados correctamente.\n    *   Reinicia el equipo.\n    *   Si el teclado tiene software propio (ej. Logitech, HP, Dell), abre el programa y revisa la configuración.\n\nSi después de intentar estos pasos el problema persiste o si observas alguno de los siguientes casos, te recomiendo que te pongas en contacto con el soporte técnico:\n\n<ul>\n  <li>El teclado no responde tras probar los pasos anteriores.</li>\n  <li>Presenta daño físico visible (teclas sueltas, cable roto, batería inflada).</li>\n  <li>Ningún puerto USB lo reconoce.</li>\n  <li>El equipo no detecta el dispositivo tras reconexión.</li>\n</ul>\n\n¿Podrías darme más detalles sobre el problema específico que tienes con tu teclado? Así podré darte una solución más precisa.\n', 'Fragmento 195 de 690cbec5e717d.pdf, Fragmento 94 de 690cbec5e717d.pdf', 2, '2025-11-06 12:24:16', '2025-11-06 17:22:27'),
(17, 'y mi teclado tampoco', '¡Vaya! Lamento escuchar que también tienes problemas con tu teclado. No te preocupes, vamos a intentar solucionarlo.\n\nPrimero, probemos algunas soluciones rápidas:\n\n<ul>\n  <li><strong>Verifica la conexión:</strong> Asegúrate de que el teclado esté bien conectado a tu computadora, ya sea por cable USB o de forma inalámbrica (revisa el receptor USB si es el caso).</li>\n  <li><strong>Reinicia el equipo:</strong> A veces, un simple reinicio puede solucionar problemas de conexión con los dispositivos.</li>\n  <li><strong>Revisa los drivers:</strong> Verifica que los drivers del teclado estén instalados correctamente.</li>\n</ul>\n\nSi tu teclado tiene funciones especiales que no responden, también puedes intentar lo siguiente:\n\n<ul>\n  <li>Si el teclado tiene software propio (ej. Logitech, HP, Dell), abre el programa y revisa la configuración.</li>\n</ul>\n\nAhora, dime, ¿el teclado tiene algún daño físico visible, como teclas sueltas o un cable roto? ¿O ninguna de las teclas responde?\n\nSi después de probar estos pasos el teclado sigue sin funcionar, o si presenta alguno de los siguientes problemas, te recomiendo que te pongas en contacto con soporte técnico:\n\n<ul>\n  <li>El teclado no responde tras probar los pasos anteriores.</li>\n  <li>Presenta daño físico visible (teclas sueltas, cable roto, batería inflada).</li>\n  <li>Ningún puerto USB lo reconoce.</li>\n  <li>El equipo no detecta el dispositivo tras reconexión.</li>\n</ul>\n', 'Fragmento 195 de 690cbec5e717d.pdf, Fragmento 180 de 690cbec5e717d.pdf', 1, '2025-11-06 12:52:01', '2025-11-06 17:52:01'),
(18, 'y mi diadema tampoco conecta', 'Entiendo, también estás teniendo problemas con tu diadema. Para ver si es necesario que la reportes a soporte técnico, revisa si presenta alguna de estas fallas:\n\n<ul>\n  <li>La diadema no enciende ni carga después de que la probaste.</li>\n  <li>No se conecta o pierde la señal constantemente con todos los dispositivos.</li>\n  <li>El micrófono o el audio no funcionan después de reconectarla.</li>\n  <li>Hay ruidos o distorsión constante, incluso con la carga completa.</li>\n  <li>Tiene daño físico o la batería está inflada.</li>\n</ul>\n\nSi presenta alguna de estas fallas, lo mejor será que la reportes a soporte técnico.\n', 'Fragmento 243 de 690cbd4f49c6f.pdf, Fragmento 242 de 690cbd4f49c6f.pdf', 1, '2025-11-06 12:54:39', '2025-11-06 17:54:39'),
(19, 'y tengo problemas con una usb no se ve en mi pantalla', 'Entiendo que tienes problemas con una USB que no se detecta en tu pantalla. Para solucionar esto, probemos algunas opciones:\n\nPrimero, asegúrate de que la USB esté bien conectada al puerto. Si ya lo está, intenta lo siguiente:\n\n<ul>\n  <li>Prueba la USB en **todos los puertos USB** de tu equipo. A veces, un puerto puede fallar mientras que otros funcionan correctamente.</li>\n  <li>Si el equipo no detecta la USB tras reconectarla a los diferentes puertos, podría haber un problema con los controladores o con la misma USB.</li>\n</ul>\n\nSi después de intentar esto el problema persiste, ¡avísame para explorar otras soluciones!\n', 'Fragmento 281 de 690cbec5e717d.pdf, Fragmento 280 de 690cbec5e717d.pdf', 1, '2025-11-06 12:55:53', '2025-11-06 17:55:53'),
(21, 'se daño mi impresora', '¡Entiendo que tu impresora se ha dañado! Para poder ayudarte mejor, necesito un poco más de información. ¿Podrías describir el daño? Por ejemplo:\n\n*   ¿Qué le pasó exactamente a la impresora?\n*   ¿Recibes algún mensaje de error?\n*   ¿Qué marca y modelo de impresora tienes?\n\nCon esta información, puedo darte una mejor orientación sobre cómo proceder.\n', 'Fragmento 291 de 690cbace21527.pdf, Fragmento 290 de 690cbace21527.pdf', 1, '2025-11-06 15:15:12', '2025-11-06 20:15:12'),
(22, 'no imprime y dice error', 'Entiendo, tu impresora no imprime y muestra un error. Aquí te dejo algunos pasos que puedes seguir para intentar solucionar el problema:\n\n<ul>\n 	<li>Reinicia la impresora y espera unos segundos.</li>\n 	<li>Verifica que no haya atascos de papel o tapas abiertas.</li>\n 	<li>Asegúrate de que los cartuchos estén bien instalados.</li>\n 	<li>Si el error continúa, anota el código y repórtalo al área de soporte.</li>\n</ul>\n\nSi después de intentar estos pasos el problema persiste, lo mejor será que te pongas en contacto con soporte técnico, especialmente si el error persiste en la pantalla.\n', 'Fragmento 257 de 690cbace21527.pdf, Fragmento 251 de 690cbace21527.pdf', 1, '2025-11-06 15:15:32', '2025-11-06 20:15:32'),
(23, 'dice imprecion en fila', 'Comprendo, el mensaje \"impresión en fila\" generalmente indica que hay trabajos de impresión pendientes que están bloqueando la cola. Aquí hay algunas cosas que puedes intentar para solucionarlo:\n\n*   **Borra la cola de impresión:** A veces, los trabajos de impresión se quedan atascados y bloquean la cola. Puedes intentar borrar la cola de impresión para solucionar esto.\n\n*   **Reinicia el servicio de cola de impresión:** Esto puede ayudar a resolver problemas relacionados con el software que gestiona la impresión.\n\nSi el problema persiste después de intentar esto, puedes contactar al área de soporte para que te ayuden a revisar la configuración de la impresora o a diagnosticar problemas más complejos.\n', 'Fragmento 53 de 690bc847efcd9.xlsx, Fragmento 54 de 690bc847efcd9.xlsx', 1, '2025-11-06 15:16:15', '2025-11-06 20:16:15'),
(24, 'si y aplica lo mismo si es inalambrico?', 'Si tu mouse no se mueve, hay varias razones por las cuales esto podría estar sucediendo. Aquí te dejo algunas posibles causas y soluciones que podrían aplicarse tanto a mouse inalámbricos como a los tradicionales:\n\n<ul>\n  <li>Polvo o suciedad en la placa de red: Es posible que haya un montón de polvo o suciedad acumulada en la placa de red del mouse, lo que puede estar interfiriendo con su funcionamiento. Puedes intentar limpiar la placa de red con un paño suave y seco.</li>\n  <li>Batería agotada: Si tu mouse es inalámbrico, asegúrate de que la batería esté cargada adecuadamente. Puedes verificar el nivel de batería en la carcasa del mouse o en la base.</li>\n  <li>Conexión inestable: Verifica que la conexión entre el mouse y tu computadora sea estable. Asegúrate de que el cable esté bien conectado y no esté dañado.</li>\n  <li>Diseño defectuoso o falla mecánica: En algunos casos, el problema puede ser debido a un diseño defectuoso o una falla mecánica en el mouse. Si el teclado tiene funciones especiales que no responden, es posible que también lo sea el mouse.</li>\n</ul>\n\nSi tu mouse es inalámbrico, también podrías intentar:\n\n<ul>\n  <li>Asegúrate de que el receptor USB esté bien conectado.</li>\n  <li>Enciende el teclado y verifica que el indicador de batería (si tiene) esté activo.</li>\n  <li>Cámbiale las baterías o recárgalo si es recargable.</li>\n  <li>Acerca el mouse al receptor (sin obstáculos entre ambos).</li>\n  <li>Si no conecta, prueba en otro puerto USB o en otro equipo.</li>\n</ul>\n\n¿Te gustaría intentar alguna de estas soluciones?', 'Fragmento 299 de 690cbd4f49c6f.pdf, Fragmento 197 de 690cbd4f49c6f.pdf', 1, '2025-11-06 15:47:58', '2025-11-06 20:47:58');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `app_config`
--
ALTER TABLE `app_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`),
  ADD KEY `idx_config_key` (`config_key`);

--
-- Indices de la tabla `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `frequent_questions`
--
ALTER TABLE `frequent_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question` (`question`(255)),
  ADD KEY `idx_times_asked` (`times_asked`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `app_config`
--
ALTER TABLE `app_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `frequent_questions`
--
ALTER TABLE `frequent_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
