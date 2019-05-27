-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  lun. 27 mai 2019 à 18:15
-- Version du serveur :  10.3.7-MariaDB
-- Version de PHP :  5.6.39

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données :  `ORM_Demo`
--
CREATE DATABASE IF NOT EXISTS `ORM_Demo` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ORM_Demo`;

-- --------------------------------------------------------

--
-- Structure de la table `customer`
--

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `ID_Customer` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Structure de la table `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `ID_Order` int(11) NOT NULL,
  `ID_Customer` int(11) NOT NULL,
  `InvoiceNumber` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Structure de la table `orderline`
--

DROP TABLE IF EXISTS `orderline`;
CREATE TABLE `orderline` (
  `ID_OrderLine` int(11) NOT NULL,
  `ID_Order` int(11) NOT NULL,
  `ID_Reference` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Structure de la table `reference`
--

DROP TABLE IF EXISTS `reference`;
CREATE TABLE `reference` (
  `ID_Reference` int(11) NOT NULL,
  `Code` varchar(10) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Price` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Structure de la table `relation`
--

DROP TABLE IF EXISTS `relation`;
CREATE TABLE `relation` (
  `TABLE_SCHEMA` varchar(50) NOT NULL,
  `TABLE_NAME` varchar(50) NOT NULL,
  `COLUMN_NAME` varchar(50) NOT NULL,
  `REFERENCED_TABLE_SCHEMA` varchar(50) NOT NULL,
  `REFERENCED_TABLE_NAME` varchar(50) NOT NULL DEFAULT '',
  `REFERENCED_COLUMN_NAME` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `relation`
--

INSERT INTO `relation` (`TABLE_SCHEMA`, `TABLE_NAME`, `COLUMN_NAME`, `REFERENCED_TABLE_SCHEMA`, `REFERENCED_TABLE_NAME`, `REFERENCED_COLUMN_NAME`) VALUES
('ORM_Demo', 'order', 'ID_Customer', 'ORM_Demo', 'customer', 'ID_Customer'),
('ORM_Demo', 'orderline', 'ID_Order', 'ORM_Demo', 'order', 'ID_Order'),
('ORM_Demo', 'orderline', 'ID_Reference', 'ORM_Demo', 'reference', 'ID_Reference');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`ID_Customer`);

--
-- Index pour la table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`ID_Order`),
  ADD KEY `ID_Customer` (`ID_Customer`);

--
-- Index pour la table `orderline`
--
ALTER TABLE `orderline`
  ADD PRIMARY KEY (`ID_OrderLine`),
  ADD KEY `ID_Reference` (`ID_Reference`),
  ADD KEY `ID_Order` (`ID_Order`);

--
-- Index pour la table `reference`
--
ALTER TABLE `reference`
  ADD PRIMARY KEY (`ID_Reference`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `customer`
--
ALTER TABLE `customer`
  MODIFY `ID_Customer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `order`
--
ALTER TABLE `order`
  MODIFY `ID_Order` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `orderline`
--
ALTER TABLE `orderline`
  MODIFY `ID_OrderLine` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reference`
--
ALTER TABLE `reference`
  MODIFY `ID_Reference` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `PK_ID_Customer` FOREIGN KEY (`ID_Customer`) REFERENCES `customer` (`ID_Customer`);

--
-- Contraintes pour la table `orderline`
--
ALTER TABLE `orderline`
  ADD CONSTRAINT `orderline_ibfk_1` FOREIGN KEY (`ID_Order`) REFERENCES `order` (`ID_Order`),
  ADD CONSTRAINT `orderline_ibfk_2` FOREIGN KEY (`ID_Reference`) REFERENCES `reference` (`ID_Reference`);
COMMIT;

