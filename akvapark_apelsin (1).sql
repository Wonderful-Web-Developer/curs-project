-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.0
-- Время создания: Дек 12 2025 г., 02:46
-- Версия сервера: 8.0.35
-- Версия PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `akvapark_apelsin`
--

-- --------------------------------------------------------

--
-- Структура таблицы `day_types`
--

CREATE TABLE `day_types` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_weekend` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `day_types`
--

INSERT INTO `day_types` (`id`, `name`, `is_weekend`) VALUES
(1, 'Будни', 0),
(2, 'Выходные/праздники', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tariff_id` int NOT NULL,
  `people_count` int NOT NULL DEFAULT '1',
  `children_under_3` int DEFAULT '0',
  `desired_date` date NOT NULL,
  `desired_time` time NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method_id` int NOT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `tariff_id`, `people_count`, `children_under_3`, `desired_date`, `desired_time`, `total_price`, `payment_method_id`, `status_id`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 5, 2, '2025-12-27', '20:00:00', 3475.00, 3, 4, '', '2025-12-07 17:42:30', '2025-12-07 19:26:21'),
(2, 3, 12, 10, 5, '2025-12-31', '16:00:00', 13000.00, 1, 3, NULL, '2025-12-09 20:32:21', '2025-12-10 21:12:02'),
(3, 3, 25, 3, 0, '2025-12-25', '14:00:00', 6150.00, 2, 4, '', '2025-12-09 23:32:00', '2025-12-09 23:32:51'),
(4, 3, 18, 2, 2, '2025-12-28', '18:00:00', 1180.00, 1, 4, '', '2025-12-09 23:40:28', '2025-12-09 23:41:30'),
(5, 3, 26, 2, 1, '2025-12-22', '16:00:00', 6200.00, 3, 3, NULL, '2025-12-10 20:56:13', '2025-12-10 20:56:44'),
(6, 3, 2, 4, 1, '2025-12-22', '18:00:00', 1960.00, 2, 3, NULL, '2025-12-10 21:22:03', '2025-12-10 21:22:17'),
(7, 3, 1, 1, 0, '2025-12-24', '14:00:00', 280.00, 1, 3, NULL, '2025-12-10 21:23:53', '2025-12-10 21:24:00'),
(8, 3, 3, 6, 2, '2025-12-30', '19:00:00', 2310.00, 3, 3, NULL, '2025-12-10 21:27:30', '2025-12-10 21:27:36'),
(9, 3, 1, 9, 3, '2025-12-22', '17:00:00', 2520.00, 2, 4, '', '2025-12-10 21:28:07', '2025-12-10 21:28:44'),
(10, 3, 1, 1, 0, '2025-12-29', '14:00:00', 280.00, 1, 1, NULL, '2025-12-10 22:04:31', '2025-12-10 22:04:31'),
(11, 3, 4, 3, 0, '2025-12-21', '14:00:00', 2085.00, 2, 1, NULL, '2025-12-10 22:04:48', '2025-12-10 22:04:48'),
(12, 3, 2, 7, 4, '2025-12-22', '18:00:00', 3430.00, 3, 1, NULL, '2025-12-10 22:05:08', '2025-12-10 22:05:08'),
(13, 3, 16, 2, 3, '2025-12-22', '18:00:00', 2980.00, 1, 4, '', '2025-12-10 22:05:29', '2025-12-10 22:23:01');

-- --------------------------------------------------------

--
-- Структура таблицы `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `name`, `code`, `is_default`) VALUES
(1, 'Новая', 'new', 1),
(2, 'В обработке', 'processing', 0),
(3, 'Отменена', 'canceled', 0),
(4, 'Выполнена', 'completed', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `code`) VALUES
(1, 'Наличными', 'CASH'),
(2, 'Перевод по номеру телефона', 'PHONE'),
(3, 'Банковской картой', 'CARD');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `order_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 5, 'Отличный сервис!', '2025-12-07 19:48:15'),
(2, 3, 4, 'Супер!', '2025-12-09 23:33:36'),
(3, 4, 4, '', '2025-12-10 21:11:43'),
(4, 9, 4, 'Во, класс!', '2025-12-10 21:29:18');

-- --------------------------------------------------------

--
-- Структура таблицы `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Категория услуги',
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `description`) VALUES
(1, 'Разовое посещение', 'Разовое посещение аквапарка'),
(2, 'Льготный тариф', 'Для льготных категорий граждан'),
(3, 'Абонемент', 'Абонементы на несколько посещений');

-- --------------------------------------------------------

--
-- Структура таблицы `tariffs`
--

CREATE TABLE `tariffs` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `ticket_type_id` int NOT NULL,
  `time_slot_id` int NOT NULL,
  `day_type_id` int NOT NULL,
  `duration_hours` int NOT NULL,
  `price_per_person` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `tariffs`
--

INSERT INTO `tariffs` (`id`, `category_id`, `ticket_type_id`, `time_slot_id`, `day_type_id`, `duration_hours`, `price_per_person`, `is_active`, `notes`) VALUES
(1, 1, 1, 1, 1, 1, 280.00, 1, 'Детский билет от 3 до 13 лет, утро без термозоны'),
(2, 1, 1, 1, 1, 2, 490.00, 1, 'Детский билет от 3 до 13 лет, утро без термозоны'),
(3, 1, 1, 1, 2, 1, 385.00, 1, 'Детский билет от 3 до 13 лет, утро без термозоны'),
(4, 1, 1, 1, 2, 2, 695.00, 1, 'Детский билет от 3 до 13 лет, утро без термозоны'),
(5, 1, 1, 2, 1, 1, 490.00, 1, 'Детский билет от 3 до 13 лет, день/вечер с термозоной'),
(6, 1, 1, 2, 1, 2, 850.00, 1, 'Детский билет от 3 до 13 лет, день/вечер с термозоной'),
(7, 1, 1, 2, 2, 1, 550.00, 1, 'Детский билет от 3 до 13 лет, день/вечер с термозоной'),
(8, 1, 1, 2, 2, 2, 950.00, 1, 'Детский билет от 3 до 13 лет, день/вечер с термозоной'),
(9, 1, 2, 1, 1, 1, 710.00, 1, 'Взрослый билет, утро без термозоны'),
(10, 1, 2, 1, 1, 2, 1100.00, 1, 'Взрослый билет, утро без термозоны'),
(11, 1, 2, 1, 2, 1, 820.00, 1, 'Взрослый билет, утро без термозоны'),
(12, 1, 2, 1, 2, 2, 1300.00, 1, 'Взрослый билет, утро без термозоны'),
(13, 1, 2, 2, 1, 1, 825.00, 1, 'Взрослый билет, день/вечер с термозоной'),
(14, 1, 2, 2, 1, 2, 1300.00, 1, 'Взрослый билет, день/вечер с термозоной'),
(15, 1, 2, 2, 2, 1, 950.00, 1, 'Взрослый билет, день/вечер с термозоной'),
(16, 1, 2, 2, 2, 2, 1490.00, 1, 'Взрослый билет, день/вечер с термозоной'),
(17, 2, 4, 1, 1, 1, 490.00, 1, 'Льготный тариф, утро без термозоны. При предъявлении документа'),
(18, 2, 4, 2, 1, 1, 590.00, 1, 'Льготный тариф, день/вечер с термозоной (с 14:00 до 16:00). При предъявлении документа'),
(19, 3, 1, 1, 1, 4, 830.00, 1, 'Детский абонемент, утро без термозоны (207 руб/час). Только будни'),
(20, 3, 1, 1, 1, 8, 1450.00, 1, 'Детский абонемент, утро без термозоны (182 руб/час). Только будни'),
(21, 3, 1, 2, 1, 4, 1100.00, 1, 'Детский абонемент, день/вечер с термозоной (275 руб/час). Только будни'),
(22, 3, 1, 2, 1, 8, 1760.00, 1, 'Детский абонемент, день/вечер с термозоной (220 руб/час). Только будни'),
(23, 3, 2, 1, 1, 4, 1760.00, 1, 'Взрослый абонемент, утро без термозоны (440 руб/час). Только будни'),
(24, 3, 2, 1, 1, 8, 2650.00, 1, 'Взрослый абонемент, утро без термозоны (331 руб/час). Только будни'),
(25, 3, 2, 2, 1, 4, 2050.00, 1, 'Взрослый абонемент, день/вечер с термозоной (512 руб/час). Только будни'),
(26, 3, 2, 2, 1, 8, 3100.00, 1, 'Взрослый абонемент, день/вечер с термозоной (388 руб/час). Только будни');

-- --------------------------------------------------------

--
-- Структура таблицы `ticket_types`
--

CREATE TABLE `ticket_types` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_age` int DEFAULT NULL,
  `max_age` int DEFAULT NULL,
  `is_free` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `ticket_types`
--

INSERT INTO `ticket_types` (`id`, `name`, `min_age`, `max_age`, `is_free`) VALUES
(1, 'Детский билет', 3, 13, 0),
(2, 'Взрослый билет', 14, NULL, 0),
(3, 'До 3 лет', NULL, 3, 1),
(4, 'Льготный', 14, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `has_thermal_zone` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `time_slots`
--

INSERT INTO `time_slots` (`id`, `name`, `start_time`, `end_time`, `has_thermal_zone`) VALUES
(1, 'Утро (без термозоны)', '10:00:00', '14:00:00', 0),
(2, 'День/вечер (с термозоной)', '14:00:00', '21:00:00', 1),
(3, 'Льготное время', '10:00:00', '16:00:00', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `birth_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `phone`, `is_admin`, `birth_date`, `created_at`) VALUES
(1, 'admin@akvapark.ru', '$2y$10$wADWQ6mb5OusoY73q6GCRugjEuhR48gU9BdI44bE2g1S1/9IZirX6', 'Администратор', '+79999999999', 1, NULL, '2025-12-07 17:02:50'),
(2, 'test@user.ru', '$2y$10$5OnoWG6wP0/s6eN9GBuK2u678Hufp452J.HtWPzQLRZgPvnzfKTu2', 'Иванов Иван Иванович', '+79161234567', 0, '1990-01-01', '2025-12-07 17:02:50'),
(3, 'user1@test.ru', '$2y$10$s17xw/3OWQdmDSxY4OcwOetc/JLkuVZqYn1sXOwJeV4N.TaPifBDK', 'Петров Петр Петрович', '+7 (916) 111-11-22', 0, '1985-05-15', '2025-12-07 17:39:20');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `day_types`
--
ALTER TABLE `day_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tariff_id` (`tariff_id`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Индексы таблицы `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Индексы таблицы `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `tariffs`
--
ALTER TABLE `tariffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `ticket_type_id` (`ticket_type_id`),
  ADD KEY `time_slot_id` (`time_slot_id`),
  ADD KEY `day_type_id` (`day_type_id`);

--
-- Индексы таблицы `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `day_types`
--
ALTER TABLE `day_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `tariffs`
--
ALTER TABLE `tariffs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT для таблицы `ticket_types`
--
ALTER TABLE `ticket_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`tariff_id`) REFERENCES `tariffs` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`);

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tariffs`
--
ALTER TABLE `tariffs`
  ADD CONSTRAINT `tariffs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`),
  ADD CONSTRAINT `tariffs_ibfk_2` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`),
  ADD CONSTRAINT `tariffs_ibfk_3` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slots` (`id`),
  ADD CONSTRAINT `tariffs_ibfk_4` FOREIGN KEY (`day_type_id`) REFERENCES `day_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
