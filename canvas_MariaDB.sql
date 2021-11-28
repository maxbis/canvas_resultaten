-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2021 at 10:57 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `canvas`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `id` int(11) NOT NULL,
  `points_possible` int(11) NOT NULL,
  `assignment_group_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_group`
--

CREATE TABLE `assignment_group` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `naam` varchar(18) NOT NULL,
  `korte_naam` varchar(6) NOT NULL,
  `pos` int(11) NOT NULL,
  `update_prio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE `module` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `position` int(11) NOT NULL,
  `items_count` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `module_def`
--

CREATE TABLE `module_def` (
  `id` int(11) NOT NULL,
  `naam` varchar(80) NOT NULL,
  `pos` int(11) NOT NULL,
  `voldaan_rule` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `resultaat`
--

CREATE TABLE `resultaat` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `module_pos` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `student_nummer` varchar(8) NOT NULL,
  `klas` varchar(2) DEFAULT NULL,
  `student_naam` varchar(200) NOT NULL,
  `ingeleverd` int(11) NOT NULL,
  `ingeleverd_eo` int(11) NOT NULL,
  `punten` decimal(4,1) NOT NULL,
  `punten_max` int(11) NOT NULL,
  `punten_eo` int(11) NOT NULL,
  `voldaan` varchar(1) NOT NULL DEFAULT '-',
  `laatste_activiteit` datetime DEFAULT '1970-01-01 00:00:00',
  `laatste_beoordeling` datetime DEFAULT '1970-01-01 00:00:00',
  `aantal_opdrachten` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `grader_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `preview_url` varchar(200) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `graded_at` datetime NOT NULL,
  `excused` tinyint(1) NOT NULL,
  `entered_score` decimal(3,1) NOT NULL,
  `workflow_state` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `login_id` varchar(80) NOT NULL,
  `student_nr` int(11) NOT NULL,
  `klas` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assignment_group`
--
ALTER TABLE `assignment_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `module_def`
--
ALTER TABLE `module_def`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resultaat`
--
ALTER TABLE `resultaat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `grader_id` (`grader_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `resultaat`
--
ALTER TABLE `resultaat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
