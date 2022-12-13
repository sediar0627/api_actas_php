CREATE DATABASE IF NOT EXISTS `api_actas`;
USE `api_actas`;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `correo` varchar(200) NOT NULL,
  `correo_verificado` int NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL,
  `password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombres` varchar(200) NOT NULL,
  `apellidos` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tipo` int NOT NULL DEFAULT '1',
  `token` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_unique` (`username`),
  UNIQUE KEY `correo_unique` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `actas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `creador_id` int NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_final` time NOT NULL,
  `responsable_id` int NOT NULL,
  `orden_del_dia` text NOT NULL,
  `descripcion_hechos` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__creador_id` (`creador_id`),
  KEY `FK__responsable_id` (`responsable_id`),
  CONSTRAINT `FK__creador_id` FOREIGN KEY (`creador_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `FK__responsable_id` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `asistentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `acta_id` int NOT NULL,
  `asistente_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__actas` (`acta_id`),
  KEY `FK__usuarios` (`asistente_id`),
  CONSTRAINT `FK__actas` FOREIGN KEY (`acta_id`) REFERENCES `actas` (`id`),
  CONSTRAINT `FK__usuarios` FOREIGN KEY (`asistente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `compromisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `acta_id` int NOT NULL,
  `responsable_id` int NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_final` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_compromisos_actas` (`acta_id`),
  KEY `FK_compromisos_usuarios` (`responsable_id`),
  CONSTRAINT `FK_compromisos_actas` FOREIGN KEY (`acta_id`) REFERENCES `actas` (`id`),
  CONSTRAINT `FK_compromisos_usuarios` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
