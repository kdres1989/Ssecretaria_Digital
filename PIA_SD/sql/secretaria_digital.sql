-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2026 a las 22:24:15
-- Versión del servidor: 10.4.17-MariaDB
-- Versión de PHP: 8.0.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `secretaria_digital`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estudiante` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grado` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidad` enum('presencial','virtual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` enum('pendiente','atendida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `notificado_usuario` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `usuario_id`, `estudiante`, `documento`, `grado`, `modalidad`, `fecha`, `hora`, `estado`, `notificado_usuario`, `creado_en`) VALUES
(1, NULL, 'Juan Manuel Rodríguez Baena', '1032454763', 'Media (10° - 11°)', 'presencial', '2026-10-30', '10:00:00', 'pendiente', 1, '2026-10-02 20:22:35'),
(2, NULL, 'Juan Manuel Rodríguez Baena', '1032454763', 'Secundaria (6° - 9°)', 'presencial', '2026-10-05', '14:00:00', 'cancelada', 1, '2026-10-02 20:25:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mensajes_contacto`
--

INSERT INTO `mensajes_contacto` (`id`, `nombre`, `correo`, `asunto`, `mensaje`, `leido`, `creado_en`) VALUES
(1, 'hfdfb', 'dgfgs@gmail.com', 'Consulta General', 'fvfudghaiushdgiuswdhñoisakkjsuhxiuhdxuahsduhasio', 1, '2026-10-22 19:07:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `publicado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `titulo`, `contenido`, `publicado_en`) VALUES
(1, 'Bienvenidos a la Secretaría Digital', 'Desde esta plataforma puedes agendar tu cita de matrícula, solicitar certificados y comunicarte con la secretaría.', '2026-10-02 20:16:33'),
(2, 'Horario de atención', 'La secretaría atiende de lunes a viernes, de 8:00 AM a 3:00 PM.', '2026-10-02 20:16:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_papeleria`
--

CREATE TABLE `solicitudes_papeleria` (
  `id` int(11) NOT NULL,
  `solicitante` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('pendiente','atendida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_papeleria`
--

INSERT INTO `solicitudes_papeleria` (`id`, `solicitante`, `tipo_documento`, `correo`, `observaciones`, `estado`, `creado_en`) VALUES
(1, 'Isabela Gil Castaño', 'Certificado de Notas (Años Anteriores)', 'isagcxx@gmail.com', NULL, 'cancelada', '2026-10-02 20:24:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('estudiante','administrativo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estudiante',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `clave_hash`, `nombre`, `rol`, `creado_en`) VALUES
(1, 'admin', '$2y$12$GTcqHFwC/bOxUrYV2dL5DuVKPBv0UENCE791rX5NGO6kvB78wVDmu', 'Secretaría', 'administrativo', '2026-10-02 20:16:33'),
(2, 'estudiante', '$2y$12$dhwB3fdeHs88KRv93ftJI.162gxlU8GqD6MGaZRvfmMwcoaaQ3EGu', 'Estudiante de prueba', 'estudiante', '2026-10-02 20:16:33'),
(3, 'ximena.suareztavera@santateresaie.edu.co', '$2y$10$eeKE8KHxhHlf4U0bxatKE.s.QY//x/sBTqPBJ3278Lmjdxtp9AJfO', 'Ximena Suárez Tavera', 'estudiante', '2026-11-20 18:41:44'),
(4, 'sofia.alvarezmunoz@santateresaie.edu.co', '$2y$10$6B4flJLi9WMJFxWZoP2eBeoar97Ld5dHX9RA/P9hiGuqVtU3.FCNW', 'Sofia Alvarez Muñoz', 'administrativo', '2026-11-20 19:57:01');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha_hora` (`fecha`,`hora`),
  ADD KEY `idx_usuario_notif` (`usuario_id`,`notificado_usuario`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitudes_papeleria`
--
ALTER TABLE `solicitudes_papeleria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solicitudes_papeleria`
--
ALTER TABLE `solicitudes_papeleria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
