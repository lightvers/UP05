-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 28 2025 г., 21:36
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `DATABASE`
--

-- --------------------------------------------------------

--
-- Структура таблицы `change_history`
--

CREATE TABLE `change_history` (
  `id` int NOT NULL,
  `entity_type` enum('equipment_movement','responsible_change') NOT NULL,
  `equipment_id` int NOT NULL,
  `from_id` int DEFAULT NULL,
  `to_id` int NOT NULL,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_by_user_id` int NOT NULL,
  `comments` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `short_name` varchar(20) DEFAULT NULL,
  `responsible_user_id` int DEFAULT NULL,
  `temp_responsible_user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `classrooms`
--

INSERT INTO `classrooms` (`id`, `name`, `short_name`, `responsible_user_id`, `temp_responsible_user_id`, `created_at`, `updated_at`) VALUES
(1, 'Лекционный зал 1', 'ЛЗ-1', NULL, NULL, '2025-06-26 13:21:53', '2025-06-26 13:21:53'),
(2, 'Лаборатория физики 203', 'ЛФ-203', NULL, NULL, '2025-06-26 13:27:07', '2025-06-26 13:27:55'),
(3, 'Компьютерный класс 101', 'КК-101', NULL, NULL, '2025-06-26 13:27:27', '2025-06-26 13:27:27'),
(4, 'Актовый зал', 'З-1', NULL, NULL, '2025-06-26 13:28:12', '2025-06-26 13:28:12'),
(5, 'Спортивный зал', 'З-2', NULL, NULL, '2025-06-26 13:28:29', '2025-06-26 13:28:29');

-- --------------------------------------------------------

--
-- Структура таблицы `consumables`
--

CREATE TABLE `consumables` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `receipt_date` date DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `responsible_user_id` int DEFAULT NULL,
  `temp_responsible_user_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `equipment_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `consumables`
--

INSERT INTO `consumables` (`id`, `name`, `description`, `receipt_date`, `photo_path`, `responsible_user_id`, `temp_responsible_user_id`, `type_id`, `equipment_id`, `created_at`, `updated_at`, `cost`) VALUES
(1, 'Кабели HDMI', 'Кабели высокой четкости 2.0, длина 2м', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-26 13:30:14', '2025-06-26 19:10:57', '1200.00'),
(2, 'Мыши беспроводные', 'Logitech M185, серые', NULL, NULL, NULL, NULL, NULL, 2, '2025-06-26 13:31:49', '2025-06-26 18:24:01', '2500.00'),
(3, 'Блок питания', '500W, 80+ Bronze', '2025-05-29', NULL, NULL, NULL, NULL, 1, '2025-06-26 13:32:23', '2025-06-26 18:13:31', '5400.00'),
(4, 'Клавиатуры', 'Redragon K552, механические', NULL, NULL, NULL, NULL, NULL, 3, '2025-06-26 13:33:06', '2025-06-26 19:15:59', '3800.00'),
(5, 'Мониторы', 'Dell 24\", Full HD, 75Hz', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-26 13:33:52', '2025-06-26 13:33:52', '21500.00'),
(8, 'Мышь', NULL, '2025-06-27', NULL, NULL, NULL, 9, 3, '2025-06-26 17:57:15', '2025-06-26 17:57:15', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `consumable_characteristics`
--

CREATE TABLE `consumable_characteristics` (
  `id` int NOT NULL,
  `consumable_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `consumable_characteristics`
--

INSERT INTO `consumable_characteristics` (`id`, `consumable_id`, `name`, `value`, `created_at`) VALUES
(3, 1, 'Длина', '2 метра', '2024-03-10 06:05:00'),
(4, 1, 'Версия', '2.1', '2024-03-10 06:05:00'),
(5, 1, 'Разрешение', '4K@120Hz', '2024-03-10 06:05:00'),
(6, 2, 'Производитель', 'Logitech', '2024-02-15 08:35:00'),
(7, 2, 'Модель', 'M185', '2024-02-15 08:35:00'),
(8, 2, 'Цвет', 'Серый', '2024-02-15 08:35:00'),
(9, 2, 'DPI', '1000', '2024-02-15 08:35:00'),
(10, 3, 'Мощность', '500W', '2024-01-05 11:25:00'),
(11, 3, 'Сертификация', '80+ Bronze', '2024-01-05 11:25:00'),
(12, 3, 'Разъемы', '24-pin, 8-pin CPU, 6+2-pin PCIe', '2024-01-05 11:25:00'),
(13, 4, 'Тип', 'Механическая', '2024-02-25 10:20:00'),
(14, 4, 'Переключатели', 'Outemu Blue', '2024-02-25 10:20:00'),
(15, 4, 'Подсветка', 'Красная', '2024-02-25 10:20:00'),
(16, 5, 'Диагональ', '24 дюйма', '2024-01-30 07:05:00'),
(17, 5, 'Разрешение', '1920x1080', '2024-01-30 07:05:00'),
(18, 5, 'Частота', '75Hz', '2024-01-30 07:05:00');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment`
--

CREATE TABLE `equipment` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `photo_path` longblob,
  `inventory_number` int NOT NULL,
  `current_classroom_id` int DEFAULT NULL,
  `responsible_user_id` int DEFAULT NULL,
  `temp_responsible_user_id` int DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `direction_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `model_id` int DEFAULT NULL,
  `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `photo_path`, `inventory_number`, `current_classroom_id`, `responsible_user_id`, `temp_responsible_user_id`, `cost`, `direction_id`, `status_id`, `model_id`, `comments`, `created_at`, `updated_at`) VALUES
(1, 'Ноутбук Dell XPS 15', NULL, 1, 3, NULL, NULL, '24567.00', NULL, 3, 1, 'Новый, в комплекте док-станция', '2025-06-26 14:20:48', '2025-06-26 14:20:48'),
(2, 'Проектор Epson EB-725Wi', NULL, 2, 1, NULL, NULL, '6045.00', NULL, NULL, NULL, 'Ультракороткофокусный, с креплением', '2025-06-26 14:21:51', '2025-06-26 14:21:51'),
(3, 'Интерактивная панель Smart Board', NULL, 3, NULL, NULL, NULL, '100000.00', NULL, 5, 2, '86 дюймов, сенсорная', '2025-06-26 14:23:02', '2025-06-26 16:59:10');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_models`
--

CREATE TABLE `equipment_models` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `type_id` int NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `equipment_models`
--

INSERT INTO `equipment_models` (`id`, `name`, `type_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'HP ProBook 450', 6, 'Ноутбук бизнес-класса', '2025-05-20 20:50:34', '2025-05-20 20:50:34'),
(2, 'Dell OptiPlex 7070', 6, 'Настольный компьютер', '2025-05-20 20:50:34', '2025-05-20 20:50:34'),
(3, 'HP LaserJet Pro MFP', 7, 'Лазерный МФУ', '2025-05-20 20:50:34', '2025-05-20 20:50:34'),
(4, 'HP ProBook 450', 6, 'Ноутбук бизнес-класса', '2025-05-20 20:51:25', '2025-05-20 20:51:25'),
(5, 'Dell OptiPlex 7070', 6, 'Настольный компьютер', '2025-05-20 20:51:25', '2025-05-20 20:51:25'),
(6, 'HP LaserJet Pro MFP', 7, 'Лазерный МФУ', '2025-05-20 20:51:25', '2025-05-20 20:51:25');

-- --------------------------------------------------------

--
-- Структура таблицы `inventories`
--

CREATE TABLE `inventories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by_user_id` int NOT NULL,
  `status` enum('planned','in_progress','completed') DEFAULT 'planned',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `inventories`
--

INSERT INTO `inventories` (`id`, `name`, `start_date`, `end_date`, `created_by_user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Годовая инвентаризация 2025', '2025-06-01 19:24:19', '2025-06-07 19:24:19', 16, 'completed', '2025-06-26 14:24:55', '2025-06-26 14:24:55'),
(2, 'Полугодовая проверка 2025', '2025-07-01 19:24:59', '2025-07-08 17:24:59', 16, 'planned', '2025-06-26 14:26:08', '2025-06-26 14:26:08');

-- --------------------------------------------------------

--
-- Структура таблицы `inventory_results`
--

CREATE TABLE `inventory_results` (
  `id` int NOT NULL,
  `inventory_id` int NOT NULL,
  `equipment_id` int DEFAULT NULL,
  `consumable_id` int DEFAULT NULL,
  `checked_by_user_id` int NOT NULL,
  `checked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ok','missing','damaged') NOT NULL,
  `comments` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `inventory_results`
--

INSERT INTO `inventory_results` (`id`, `inventory_id`, `equipment_id`, `consumable_id`, `checked_by_user_id`, `checked_at`, `status`, `comments`) VALUES
(1, 1, 1, 5, 16, '2025-06-26 17:28:04', 'ok', NULL),
(2, 2, 3, 3, 16, '2025-06-26 17:28:43', 'damaged', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `network_settings`
--

CREATE TABLE `network_settings` (
  `id` int NOT NULL,
  `equipment_id` int NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `subnet_mask` varchar(15) NOT NULL,
  `gateway` varchar(15) DEFAULT NULL,
  `dns1` varchar(15) DEFAULT NULL,
  `dns2` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `network_settings`
--

INSERT INTO `network_settings` (`id`, `equipment_id`, `ip_address`, `subnet_mask`, `gateway`, `dns1`, `dns2`, `created_at`, `updated_at`) VALUES
(1, 3, '192.168.1.10', '255.255.255.0', NULL, NULL, NULL, '2025-06-26 14:31:55', '2025-06-26 14:31:55'),
(2, 1, '192.168.1.15', '255.255.255.0', NULL, NULL, NULL, '2025-06-26 14:32:16', '2025-06-26 14:32:16'),
(3, 2, '192.168.1.20', '255.255.255.0', NULL, NULL, NULL, '2025-06-26 14:32:30', '2025-06-26 14:32:30');

-- --------------------------------------------------------

--
-- Структура таблицы `reference_items`
--

CREATE TABLE `reference_items` (
  `id` int NOT NULL,
  `type` enum('direction','status','equipment_type','consumable_type','developer') NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `reference_items`
--

INSERT INTO `reference_items` (`id`, `type`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'direction', 'IT', 'Информационные технологии', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(2, 'direction', 'Science', 'Научное направление', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(3, 'status', 'Используется', 'Активное оборудование', '2025-05-20 20:50:06', '2025-06-26 16:49:40'),
(5, 'status', 'В ремонте', 'В ремонте', '2025-05-20 20:50:06', '2025-06-26 16:49:32'),
(6, 'equipment_type', 'Computer', 'Компьютерная техника', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(7, 'equipment_type', 'Printer', 'Принтеры и МФУ', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(8, 'consumable_type', 'Cartridge', 'Картриджи для принтеров', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(9, 'consumable_type', 'Paper', 'Бумага', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(10, 'developer', 'Microsoft', 'Microsoft Corporation', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(11, 'developer', 'HP', 'Hewlett-Packard', '2025-05-20 20:50:06', '2025-05-20 20:50:06'),
(13, 'status', 'Сломан', 'Сломанный', '2025-05-23 17:36:08', '2025-05-23 17:36:57');

-- --------------------------------------------------------

--
-- Структура таблицы `software`
--

CREATE TABLE `software` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `developer_id` int DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `software`
--

INSERT INTO `software` (`id`, `name`, `developer_id`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Windows 10 Pro', 10, '21H2', '2025-05-20 20:51:50', '2025-05-20 20:51:50'),
(2, 'Microsoft Office', 10, '2019', '2025-05-20 20:51:50', '2025-05-20 20:51:50'),
(3, 'HP Print Driver', 11, '5.1', '2025-05-20 20:51:50', '2025-05-20 20:51:50');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','employee') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `last_name`, `first_name`, `middle_name`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(16, 'Kolaaa', '$2y$10$m9aog921bvm4sTen8PoYbuGvi4w7uOHOWhO6NFCBoN.MFs7XybM.W', 'admin', 'kola@example.com', 'Колав', 'Кола', 'Колавич', '12345678', 'Колская, 23', '2025-05-23 17:11:02', '2025-05-23 18:47:35');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `change_history`
--
ALTER TABLE `change_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by_user_id` (`changed_by_user_id`),
  ADD KEY `idx_change_history_equipment` (`equipment_id`),
  ADD KEY `idx_change_history_type` (`entity_type`);

--
-- Индексы таблицы `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `temp_responsible_user_id` (`temp_responsible_user_id`),
  ADD KEY `idx_classrooms_responsible` (`responsible_user_id`);

--
-- Индексы таблицы `consumables`
--
ALTER TABLE `consumables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `responsible_user_id` (`responsible_user_id`),
  ADD KEY `temp_responsible_user_id` (`temp_responsible_user_id`),
  ADD KEY `idx_consumables_type` (`type_id`),
  ADD KEY `idx_consumables_equipment` (`equipment_id`);

--
-- Индексы таблицы `consumable_characteristics`
--
ALTER TABLE `consumable_characteristics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consumable_chars` (`consumable_id`);

--
-- Индексы таблицы `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_number` (`inventory_number`),
  ADD KEY `responsible_user_id` (`responsible_user_id`),
  ADD KEY `temp_responsible_user_id` (`temp_responsible_user_id`),
  ADD KEY `direction_id` (`direction_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `idx_equipment_inventory` (`inventory_number`),
  ADD KEY `idx_equipment_classroom` (`current_classroom_id`),
  ADD KEY `idx_equipment_status` (`status_id`);

--
-- Индексы таблицы `equipment_models`
--
ALTER TABLE `equipment_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_equipment_models_type` (`type_id`);

--
-- Индексы таблицы `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`),
  ADD KEY `idx_inventories_status` (`status`),
  ADD KEY `idx_inventories_dates` (`start_date`,`end_date`);

--
-- Индексы таблицы `inventory_results`
--
ALTER TABLE `inventory_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_inventory_item` (`inventory_id`,`equipment_id`,`consumable_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `consumable_id` (`consumable_id`),
  ADD KEY `checked_by_user_id` (`checked_by_user_id`),
  ADD KEY `idx_inventory_results_status` (`status`);

--
-- Индексы таблицы `network_settings`
--
ALTER TABLE `network_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_address` (`ip_address`),
  ADD KEY `idx_network_equipment` (`equipment_id`),
  ADD KEY `idx_network_ip` (`ip_address`);

--
-- Индексы таблицы `reference_items`
--
ALTER TABLE `reference_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reference_items_type_name` (`type`,`name`),
  ADD KEY `idx_reference_items_type` (`type`);

--
-- Индексы таблицы `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_software_name_version` (`name`,`version`),
  ADD KEY `idx_software_developer` (`developer_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `change_history`
--
ALTER TABLE `change_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `consumables`
--
ALTER TABLE `consumables`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `consumable_characteristics`
--
ALTER TABLE `consumable_characteristics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `equipment_models`
--
ALTER TABLE `equipment_models`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `inventory_results`
--
ALTER TABLE `inventory_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `network_settings`
--
ALTER TABLE `network_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `reference_items`
--
ALTER TABLE `reference_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `software`
--
ALTER TABLE `software`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `change_history`
--
ALTER TABLE `change_history`
  ADD CONSTRAINT `change_history_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `change_history_ibfk_2` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `classrooms`
--
ALTER TABLE `classrooms`
  ADD CONSTRAINT `classrooms_ibfk_1` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `classrooms_ibfk_2` FOREIGN KEY (`temp_responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `consumables`
--
ALTER TABLE `consumables`
  ADD CONSTRAINT `consumables_ibfk_1` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consumables_ibfk_2` FOREIGN KEY (`temp_responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consumables_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `reference_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consumables_ibfk_4` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `consumable_characteristics`
--
ALTER TABLE `consumable_characteristics`
  ADD CONSTRAINT `consumable_characteristics_ibfk_1` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`current_classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_3` FOREIGN KEY (`temp_responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_4` FOREIGN KEY (`direction_id`) REFERENCES `reference_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_5` FOREIGN KEY (`status_id`) REFERENCES `reference_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_6` FOREIGN KEY (`model_id`) REFERENCES `equipment_models` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_equipment_classroom` FOREIGN KEY (`current_classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `equipment_models`
--
ALTER TABLE `equipment_models`
  ADD CONSTRAINT `equipment_models_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `reference_items` (`id`);

--
-- Ограничения внешнего ключа таблицы `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `inventory_results`
--
ALTER TABLE `inventory_results`
  ADD CONSTRAINT `inventory_results_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_results_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_results_ibfk_3` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_results_ibfk_4` FOREIGN KEY (`checked_by_user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `network_settings`
--
ALTER TABLE `network_settings`
  ADD CONSTRAINT `network_settings_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `software`
--
ALTER TABLE `software`
  ADD CONSTRAINT `software_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `reference_items` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
