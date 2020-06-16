-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost
-- Généré le :  Mar 16 Juin 2020 à 18:07
-- Version du serveur :  5.7.26-0ubuntu0.18.04.1
-- Version de PHP :  7.0.33-8+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `benevole`
--

-- --------------------------------------------------------

--
-- Structure de la table `mib_account`
--

CREATE TABLE `mib_account` (
  `id` int(11) NOT NULL,
  `matricule` varchar(100) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `birthdate` datetime DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `news` int(1) NOT NULL DEFAULT '0',
  `dateCreate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `mib_account`
--

INSERT INTO `mib_account` (`id`, `matricule`, `lastname`, `firstname`, `company`, `place`, `birthdate`, `email`, `password`, `news`, `dateCreate`) VALUES
(4, '00000021', 'AUCORDIER', 'CHRISTIAN', NULL, NULL, NULL, 'support@2boandco.com', '$2y$10$rlFD6LWJtaYj3OKBhS2fAOhzfWC/mOwTKvak49JuscveK.hl/LtpG', 0, '2019-05-03 00:00:00'),
(5, '00008467', 'BOTTENWIESER', 'JULIE', NULL, NULL, NULL, 'julie.bottenwieser@stmichel.fr', '$2y$10$Fzicya3miofWnj8QG.iqzejpJxR3u7ol5E2pICC6x5bZq31iWVA6W', 0, '2019-05-13 00:00:00'),
(6, '00008137', 'BONNIN', 'SYLVAIN', NULL, NULL, NULL, 'sylvain.bonnin@stmichel.fr', '$2y$10$4jJ/9tGuUcqLBRiX/ChSLujqTBqrzEswW8jPaXvm9GDfLPoHIu7ua', 0, '2019-05-13 00:00:00'),
(7, '00008204', 'SIMON', 'LUDIVINE', NULL, NULL, NULL, 'simonludivine78@gmail.com', '$2y$10$nI6wFAx2/HeZ6Q3SacVxJecwcSUDOo1oBCnZItUF2bEPSadkKz5R6', 0, '2019-05-13 00:00:00'),
(8, '00001270', 'DESIGNOLLE', 'ALEXIS', NULL, NULL, NULL, 'alexis.designolle@stmichel.fr', '$2y$10$BFuQASKvqnuSIIBIxMT0vuY/SxI7HIpkx76IEm2Mmy2fIiDM/5.9G', 0, '2019-05-13 00:00:00'),
(9, '00001370', 'RICHARD', 'CYRIELLE', NULL, NULL, NULL, 'cyrielle.richard@stmichel.fr', '$2y$10$2q1x/lOAp/vGFg6tzC/W5upCvnRiwaumQP.TRIRGzpz1QOZIqaSYC', 1, '2019-05-13 00:00:00'),
(10, '00004422', 'PEGAIN', 'TONY', NULL, NULL, NULL, 'tony.pegain@stmichel.fr', '$2y$10$9Pk9hh4Wb/uD7EANFrCmO.cSnt5TKe44t5pOk9bSMRJNOs/4GYAjS', 1, '2019-05-13 00:00:00'),
(11, '00006276', 'MAZIERES', 'CEDRIC', NULL, NULL, NULL, 'cedric41700@gmail.com', '$2y$10$gN2HFvoCL8xQ6lyNrjLLyOGENM2DuX3L7ACYXhzsxjTp6lCxgMFvC', 0, '2019-05-14 00:00:00'),
(12, '00005311', 'BELLAIS', 'VINCENT', NULL, NULL, NULL, 'vincent.bellais@stmichel.fr', '$2y$10$D6l3SaOp5XRFx9rj1fdmbeRsCSZcLYjc6dW8Wc.ie54OIf/mGG7b6', 0, '2019-05-14 00:00:00'),
(13, '00000628', 'MAILLOT', 'AURELIE', NULL, NULL, NULL, 'aurelie.maillot@stmichel.fr', '$2y$10$dG8vtet2oxz6u6bDoS/L4ul6MWkeYv4W/TRx4JsfNQ9.0jWbZiXEq', 0, '2019-05-14 00:00:00'),
(14, '00006410', 'GUENNEGUES', 'JULIE', NULL, NULL, NULL, 'julie.guennegues@stmichel.fr', '$2y$10$Wwhg/HY.k2NqqWfxFZmcJeEzcZeAvp5Oxt0YLrEbvZwLHyNh1q1P.', 0, '2019-05-15 00:00:00'),
(15, '00003039', 'LOUBET', 'LAURENCE', NULL, NULL, NULL, 'Laurence.LOUBET@stmichel.fr', '$2y$10$T5UAO8ySkykFaVAiE8J1w./sm4J4SH5qxc78t50p5R6vPPueWxdSG', 0, '2019-05-15 00:00:00'),
(16, '00000015', 'ARDOUIN', 'MARYSE', NULL, NULL, NULL, 'Maryse.ARDOUIN@stmichel.fr', '$2y$10$tKTjRHYfY8vVzMXQMQ4cquWjyok.AYMLZNAKJmROgXIIzKHP7Qc/i', 0, '2019-05-15 00:00:00'),
(17, '00005316', 'GUILLOIS', 'VANESSA', NULL, NULL, NULL, 'vanessa.g41@neuf.fr', '$2y$10$EFP9Yf1Ev5NWv/weFd5Nd.rpoBX/vdGRXu4dk4YHSkOBOEWFRgHx6', 0, '2019-05-16 00:00:00'),
(18, '00000524', 'RINFERT', 'MELANIE', NULL, NULL, NULL, 'melanie.rinfert@stmichel.fr', '$2y$10$9rAmcFQLxC/cznJKtEhAT.3PD.e/BtX4H/g9UG0KQ1wIAUmSpMt2W', 0, '2019-05-17 00:00:00'),
(32, '00001259', 'MARGAIN', 'MAXIME', NULL, NULL, NULL, 'maxime.margain@stmichel.fr', '$2y$10$Uv/4vUSDFtibwYZiJhW.7utJ3a33Jeq7gi4NZPzdrJls0/n0au/zq', 0, '2019-05-20 00:00:00'),
(33, '00009198', 'GARSMEUR', 'FRANCK', NULL, NULL, NULL, 'franck.garsmeur@stmichel.fr', '$2y$10$BDqMLUxHr5UlMRQOarSq.eRbgPB5KDg06SA0wD6tFyxdZNZm94BoW', 0, '2019-05-20 00:00:00'),
(34, '00099999', 'COMBES', 'ANTOINE', NULL, NULL, NULL, 'antoine.combes@stmichel.fr', '$2y$10$T6ieHqT2XFePh2G8rvLZfOZ6WXTHGsclRTIPEDzcCeoi9iwXOMFrO', 0, '2019-05-20 00:00:00'),
(35, '00001064', 'LOYER', 'CECILE', NULL, NULL, NULL, 'cecile.loyer@stmichel.fr', '$2y$10$C7UoGwkvLeelOyvdw13.PueGaM4H1xClF1GMU/W8RBy5s5SSzWbQq', 0, '2019-05-20 00:00:00'),
(45, '123456999', 'JEZE', 'Yann', NULL, NULL, NULL, 'yannjez@gmail.com', '$2y$10$J31ub1/hyooKJ4CfvRR7t.U79ULubfWqAR2UZ4.vQdrueY1P/8Li.', 1, '2019-05-21 00:00:00'),
(46, '123456999', 'JEZE', 'Yann', NULL, NULL, NULL, 'yannjez7999@gmail.com', '$2y$10$C/R9UPerbHm041h8xKLlyepR7fGl1iEWyd4kPV8k3C/Bhdq5zG4Ci', 0, '2019-05-21 00:00:00'),
(47, '00000906', 'VASLIN', 'BENOIT', NULL, NULL, NULL, 'benoit.vaslin@stmichel.fr', '$2y$10$z21LgNzJVF6JqFKx6dEojucNJm9I6aUtY9d2WOKST4ASNUxMcpqpO', 1, '2019-05-21 00:00:00'),
(49, '123456999', 'JEZE', 'Yann', NULL, NULL, NULL, 'yannjez11111@gmail.com', '$2y$10$9rh1I6dHtfHowedmr6BoHepsAMN49d32K09..eyg2tr/TY9jfAf5W', 1, '2019-05-25 00:00:00'),
(50, '123456999', 'JEZE', 'Yann', NULL, NULL, NULL, 'yannjez22222@gmail.com', '$2y$10$8PLbmQUiptQT3h4kSAW5yO8PYt/kYkbGj0W58/fUbZIVgUqjIKQZ2', 0, '2019-05-25 00:00:00'),
(51, '123456999', 'JEZE', 'Yann', NULL, NULL, NULL, 'yannjez236548@gmail.com', '$2y$10$g9xgLZGbd8bvcS9bEB.ybOZqHMvMfp6/G.IxkCThOQCt2nTeSt5aO', 0, '2019-05-25 00:00:00'),
(54, '00008645', 'BRINET', 'NOLWENN', NULL, NULL, NULL, 'nolwennbrinet@outlook.fr', '$2y$10$.NYr1EVMs2vOXFAutkrJ6eQejlTjxCaik8E2sqquWSmRF/2.NaGDO', 0, '2019-05-25 00:00:00'),
(56, '00002520', 'LUCAS', 'ANNABELLE', NULL, NULL, NULL, 'annabelle.lucas@stmichel.fr', '$2y$10$S.MIB9MKu1gr1Fih8KDqRO0lk1EGaMzV9LfB.eYF/q/5epaTxBNZu', 1, '2019-06-14 00:00:00'),
(57, '00006074', 'ROUSSEAU', 'BRUNO', NULL, NULL, NULL, 'bruno.rousseau@stmichel.fr', '$2y$10$bAiFGa9Yf7YEPMqwmitDyOQUcrn6jD3H9HaIw8dGN0LqY0M6Hw3kq', 1, '2019-07-18 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `mib_blog`
--

CREATE TABLE `mib_blog` (
  `id` int(12) NOT NULL,
  `value` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mib_configs`
--

CREATE TABLE `mib_configs` (
  `id` int(10) UNSIGNED NOT NULL,
  `conf_type` varchar(255) DEFAULT NULL,
  `conf_ref` varchar(255) DEFAULT NULL,
  `conf_name` varchar(255) DEFAULT NULL,
  `conf_value` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `mib_configs`
--

INSERT INTO `mib_configs` (`id`, `conf_type`, `conf_ref`, `conf_name`, `conf_value`) VALUES
(1, 'system', NULL, 'site_title', 'Mibbo'),
(2, 'system', NULL, 'server_timezone', 'Europe/Paris'),
(3, 'system', NULL, 'timeout_visit', '1800'),
(4, 'system', NULL, 'timeout_online', '600'),
(5, 'system', NULL, 'site_email', 'support@2boandco.com'),
(6, 'system', NULL, 'default_user_group', '3'),
(7, 'system', NULL, 'bo_color', '1e9cd3');

-- --------------------------------------------------------

--
-- Structure de la table `mib_found`
--

CREATE TABLE `mib_found` (
  `id` int(12) NOT NULL,
  `value` json DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mib_groups`
--

CREATE TABLE `mib_groups` (
  `g_id` int(10) UNSIGNED NOT NULL,
  `g_title` varchar(50) NOT NULL DEFAULT '',
  `g_bo_perms` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `mib_groups`
--

INSERT INTO `mib_groups` (`g_id`, `g_title`, `g_bo_perms`) VALUES
(1, 'Administrateurs', NULL),
(2, 'Invités', NULL),
(3, 'Membres', NULL),
(4, 'Responsable MAJ', 'YTo0OntzOjExOiJhZG1pbl91c2VycyI7YToyOntzOjQ6InJlYWQiO2k6MTtzOjU6IndyaXRlIjtpOjE7fXM6MTI6InBhZ2VfbWFuYWdlciI7YToyOntzOjQ6InJlYWQiO2k6MTtzOjU6IndyaXRlIjtpOjE7fXM6NDoiYmxvZyI7YToyOntzOjQ6InJlYWQiO2k6MTtzOjU6IndyaXRlIjtpOjE7fXM6MjoiYm8iO2I6MTt9fGE6Nzp7czo2OiJNb25kYXkiO2E6Mzp7czo0OiJ0eXBlIjtpOjE7czo1OiJzdGFydCI7aTowO3M6NjoiZmluaXNoIjtpOjA7fXM6NzoiVHVlc2RheSI7YTozOntzOjQ6InR5cGUiO2k6MTtzOjU6InN0YXJ0IjtpOjA7czo2OiJmaW5pc2giO2k6MDt9czo5OiJXZWRuZXNkYXkiO2E6Mzp7czo0OiJ0eXBlIjtpOjE7czo1OiJzdGFydCI7aTowO3M6NjoiZmluaXNoIjtpOjA7fXM6ODoiVGh1cnNkYXkiO2E6Mzp7czo0OiJ0eXBlIjtpOjE7czo1OiJzdGFydCI7aTowO3M6NjoiZmluaXNoIjtpOjA7fXM6NjoiRnJpZGF5IjthOjM6e3M6NDoidHlwZSI7aToxO3M6NToic3RhcnQiO2k6MDtzOjY6ImZpbmlzaCI7aTowO31zOjg6IlNhdHVyZGF5IjthOjM6e3M6NDoidHlwZSI7aToxO3M6NToic3RhcnQiO2k6MDtzOjY6ImZpbmlzaCI7aTowO31zOjY6IlN1bmRheSI7YTozOntzOjQ6InR5cGUiO2k6MTtzOjU6InN0YXJ0IjtpOjA7czo2OiJmaW5pc2giO2k6MDt9fQ==');

-- --------------------------------------------------------

--
-- Structure de la table `mib_interaction`
--

CREATE TABLE `mib_interaction` (
  `interaction_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `contact_alpha` varchar(50) DEFAULT NULL,
  `contact_investor` varchar(50) DEFAULT NULL,
  `notes_interactions` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `mib_lead`
--

CREATE TABLE `mib_lead` (
  `id` int(11) NOT NULL,
  `investor_name` varchar(50) NOT NULL,
  `investor_type` varchar(50) NOT NULL,
  `street_name` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `zip_code` int(5) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `alpha_relationship` varchar(50) DEFAULT NULL,
  `alpha_investor` varchar(50) DEFAULT NULL,
  `fund_management` varchar(50) DEFAULT NULL,
  `private_equity_allocation` varchar(50) DEFAULT NULL,
  `typical_bite_size` varchar(50) DEFAULT NULL,
  `co_investment_appetite` varchar(50) DEFAULT NULL,
  `co_investment_bite_size` varchar(50) DEFAULT NULL,
  `invested_in` varchar(50) DEFAULT NULL,
  `overview_investor` text,
  `alpha_history` text,
  `title` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `jobtitle` varchar(50) DEFAULT NULL,
  `number_office` varchar(21) DEFAULT '+33 ',
  `number_mobile` varchar(21) DEFAULT '+33',
  `notes_contact` text,
  `date` date DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `contact_alpha` varchar(50) DEFAULT NULL,
  `contact_investor` varchar(50) DEFAULT NULL,
  `notes_interactions` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `mib_online`
--

CREATE TABLE `mib_online` (
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `ident` varchar(200) NOT NULL DEFAULT '',
  `logged` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `idle` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Contenu de la table `mib_online`
--

INSERT INTO `mib_online` (`user_id`, `ident`, `logged`, `idle`) VALUES
(1, '127.0.0.1', 1592319198, 0);

-- --------------------------------------------------------

--
-- Structure de la table `mib_options`
--

CREATE TABLE `mib_options` (
  `autoid` int(12) NOT NULL,
  `key` varchar(50) NOT NULL,
  `id` varchar(25) DEFAULT NULL,
  `lang` varchar(6) DEFAULT NULL,
  `value` json DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `mib_options`
--

INSERT INTO `mib_options` (`autoid`, `key`, `id`, `lang`, `value`) VALUES
(21, 'pageAssociation', NULL, NULL, '[{\"key\": \"page-responsible-investing\", \"page\": \"fr_responsible_investing\", \"handler\": \"page\"}, {\"key\": \"page-strategy\", \"page\": \"fr_our_strategy\", \"handler\": \"page\"}, {\"key\": \"page-base\", \"page\": \"en_index\", \"handler\": \"page\"}]');

-- --------------------------------------------------------

--
-- Structure de la table `mib_prospective`
--

CREATE TABLE `mib_prospective` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `investor_name` varchar(50) NOT NULL,
  `investor_type` varchar(50) DEFAULT NULL,
  `street_name` text,
  `zip_code` varchar(10) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `alpha_relationship` varchar(50) DEFAULT NULL,
  `alpha_investor` varchar(50) DEFAULT NULL,
  `apef4` int(11) DEFAULT NULL,
  `apef5` int(11) DEFAULT NULL,
  `apef6` int(11) DEFAULT NULL,
  `apef7` int(11) DEFAULT NULL,
  `previous` varchar(255) DEFAULT NULL,
  `fund_management` varchar(50) DEFAULT NULL,
  `private_equity_allocation` varchar(50) DEFAULT NULL,
  `typical_bite_size` varchar(50) DEFAULT NULL,
  `co_investment_appetite` varchar(50) DEFAULT NULL,
  `co_investment_bite_size` varchar(50) DEFAULT NULL,
  `invested_in` varchar(50) DEFAULT NULL,
  `overview_investor` text,
  `alpha_history` text,
  `magenta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `mib_prospective`
--

INSERT INTO `mib_prospective` (`id`, `status`, `investor_name`, `investor_type`, `street_name`, `zip_code`, `city`, `country`, `website`, `alpha_relationship`, `alpha_investor`, `apef4`, `apef5`, `apef6`, `apef7`, `previous`, `fund_management`, `private_equity_allocation`, `typical_bite_size`, `co_investment_appetite`, `co_investment_bite_size`, `invested_in`, `overview_investor`, `alpha_history`, `magenta`) VALUES
(1, 0, 'CAISSE DE DÉPÔT ET PLACEMENT DU QUÉBEC (CDPQ)', 'FUND OF FUNDS', '65, rue Sainte-Anne', 'G1R 3X5', 'QUÉBEC', 'CA', NULL, 'Helena Quinn', NULL, NULL, NULL, NULL, NULL, NULL, '$310bn', '13.9% ($42.9bn)', '$150-250m', '1', 'Unlimited capacity', 'Permira, EQT, Ardian, Lion Capital', 'Caisse de dépôt et placement du Québec (CDPQ) was established in 1965 and actively manages funds on behalf of its depositors in Québec, which are principally public and private pensions and insurance plans. CDPQ is a large investor in the private equity asset class. It invests in a variety of fund types, including buyout, distressed debt and growth funds. CDPQ has previously invested in fund of funds and venture capital vehicles, but will no longer seek such opportunities. Geographically, the asset manager\\\'s portfolio is split between the US, Quebec, non-Quebec Canada, Europe, and Asia and the Pacific region. \\n\\nCDPQ is an active investor in private equity as part of its general alternatives strategy. The public pension fund allocates approximately 13.9% ($42.9bn) of its total assets to the asset class. CDPQ is focused on quality partnerships and looks to be an active shareholder through its investment although 75% of their portfolio is in direct investments.\\nCDPQ have invested in Europeans funds such as Permira, EQT, Ardian, Lion Capital etc. Their typical ticket size if $150-250m (although for a $1bn size fund they may reduce their ticket size) and have an AuM of c.$300bn. Their exposure to Europe is approximately 26% of their portfolio.', 'Capstone engaged with the Paris team during the fundraise for APEF 7. There were very positive tones but ultimately, it appears that they were clear in the second meeting that the fund size was too small for them.', 0),
(8, 0, 'NAME', 'BANK', NULL, NULL, NULL, 'FR', NULL, 'Helena Quinn', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `mib_sitepage`
--

CREATE TABLE `mib_sitepage` (
  `autoid` int(12) NOT NULL,
  `key` varchar(200) NOT NULL,
  `id` varchar(200) DEFAULT NULL,
  `lang` varchar(6) DEFAULT NULL,
  `value` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `mib_sitepage`
--

INSERT INTO `mib_sitepage` (`autoid`, `key`, `id`, `lang`, `value`) VALUES
(1, 'page-about', 'en_about_us', 'en', '{\"pageTitle\": \"WHO WE  ARE\", \"PageParagraph1Title\": \"\", \"PageParagraph2Title\": \"EXPERIENCE\", \"PageParagraph3Title\": \"VALUE CREATION\", \"PageParagraph1Content\": \"<div><p>Alpha is an independent Pan-European Private Equity firm, specialized in mid-market buyouts with &euro;2 bn under management.</p><h3>Pan-European firm</h3><ul><li>Main focus in Italy, France, Germany, Benelux, Switzerland</li><li>Proven track record in executing crossborder transactions</li></ul><h3>Independent</h3><ul><li>Short decision process</li><li>Open to all industries / No industry bias</li><li>High flexibility to adapt to deal specifics</li></ul><h3>Entrepreneurs for Entrepreneurs</h3><ul><li>Emphasis on companies where there is an opportunity to bring value</li><li>Entrepreneurial mindset translating into a deep understanding of Entrepreneurs / Managers</li><li>Successful track record in helping Families / Managers achieve their goals (successions / transitions)</li><li>Focus on aligning everyone&rsquo;s interests</li></ul></div>\", \"PageParagraph2Content\": \"<p>Over 130 transactions in Continental Europe, in all sectors</p><p>Experience in managing different types of transactions and complex situations, across different economic cycles</p>\", \"PageParagraph3Content\": \"<p><strong>A seasoned team, able to grow portfolio companies to the next level</strong></p><p><strong>We work intensively on portfolio companies through build-ups, performance enhancement, team reinforcement, also leveraging on our network of operating partners and successful CEOs</strong></p><p><strong>Strong and consistent track record across different economic cycles</strong></p>\", \"PageParagraph4Content\": \"<p>dqd</p>\", \"PageParagraph5Content\": \"<p>qdq</p>\"}'),
(2, 'page-portfolio', 'en_test', 'en', '{\"pageTitle\": \"ezr\", \"pageTagLine\": \"\", \"PageParagraph3Title\": \"<p><strong>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod dolore magna aliquyam erat, sed diam voluptua. At vero eos et. </strong></p>\", \"PageParagraph1Content\": \"<p><em>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et </em></p>\", \"PageParagraph2Content\": \"<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata</p>\"}'),
(3, 'page-responsible-investing', 'en_test', 'en', '{\"pageTitle\": \"GERGE\", \"pageTagLine\": \"&lt;c\", \"PageParagraph3Title\": \"<p>Lead text to introduce dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Lead text to introduce dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Lead text to introduce dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>\", \"PageParagraph1Content\": \"<p>Lead text to introduce dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>\", \"PageParagraph2Content\": \"<p>Lead text to introduce dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Lead text to introduce dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>\"}'),
(4, 'page-responsible-investing', 'en_about_us', 'en', '{\"pageTitle\": \"SDF\", \"pageTagLine\": \"FZF\", \"PageParagraph3Title\": \"<p>EZF</p>\", \"PageParagraph1Content\": \"<p>ZF</p>\", \"PageParagraph2Content\": \"<p>FZE</p>\"}'),
(5, 'page-about', 'fr_about_us', 'fr', '{\"pageTitle\": \"Who we are\", \"PageParagraph1Title\": \"Who we are\", \"PageParagraph2Title\": \"EXPERIENCE\", \"PageParagraph3Title\": \"VALUE CREATION\", \"PageParagraph1Content\": \"<p>Alpha is an independent private equity investor focused on investing in global and/or innovative businesses in France, Italy, Germany, Switzerland and Benelux with &euro;2bn under management.</p><p>Alpha invests in businesses where we see the opportunity to create significant value and as a result real returns for our investors, by executing the Alpha strategy. We work intensively on our portfolio companies so a strong relationship and the support of the management team is paramount.</p>\", \"PageParagraph2Content\": \"<p>Over 130 transactions in Continental Europe, in all sectors</p><p>Experience in managing different types of transactions and complex situations, across different economic cycles</p>\", \"PageParagraph3Content\": \"<p>A seasoned team, able to grow portfolio companies to the next level</p><p>We work intensively on portfolio companies through build-ups, performance enhancement, team reinforcement, also leveraging on our network of operating partners and successful CEOs</p><p>Strong and consistent track record across different economic cycles</p>\", \"PageParagraph4Content\": \"\", \"PageParagraph5Content\": \"\"}'),
(6, 'page-responsible-investing', 'fr_responsible_investing', 'fr', '{\"pageTitle\": \"Responsible Investing\", \"pageTagLine\": \"\", \"PageParagraphValor\": \"Alpha became a signatory to the united Nationu0027s Principles for Responsible Investment ( UN PRI ) in November 2011\", \"PageParagraph3Title\": \"<p>lala</p>\", \"PageParagraph1Content\": \"<p>At Alpha, we want to make a positive impact on the world around us through our investments. Responsible Investment is a central tenet to the success of our portfolio companies and as a result, the value we create for investors. Working together with the management teams of our portfolio companies puts us in a stronger position to face the challenges of the future such as limited resources, climate change and regulatory change.</p>\", \"PageParagraph2Content\": \"<p>Our approach is executed throughout the investment cycle, from the identification of potential deals to the sales processes. Our approach to responsibly investing has these core principles:</p><ul><li>&nbsp;In the pre-investment phase, we perform full ESG due diligence on every portfolio company.</li><li>The outcome of the due-diligence is evaluated by the Investment Committee as well as the ESG Committee. We establish whether there are any &ldquo;show-stoppers&rdquo; &ndash; ESG issues that we feel pose an unacceptable risk to our ability to execute the Alpha plan or to protect our investors&rsquo; capital.</li><li>&nbsp;Based on the ESG due diligence, we work with the management of the portfolio company to address the risks, if any, identified in the ESG due diligence as well as establishing measurable targets for improvement.</li><li>&nbsp;We report this progress to our investors and to the stakeholders of the portfolio company thereby holding ourselves and our portfolio companies accountable.</li></ul><p>Responsible Investment is not just a step of our investment process, it is part of the DNA of an Alpha deal. You can find our ESG Policy HERE</p>\", \"PageParagraph3Content\": \"\", \"PageParagraph3content\": \"<p>kjkbjh</p>\", \"PageParagraph4Content\": \"\"}'),
(7, 'page-base', 'fr_testt', 'fr', '{\"pageTitle\": \"gfd\", \"PageParagraph1Title\": \"gdggvxc\", \"PageParagraph2Title\": \"DS\", \"PageParagraph3Title\": \"DFS\", \"PageParagraph1Content\": \"<p>&quot;At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.&quot;</p>\", \"PageParagraph2Content\": \"<p>&quot;At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.&quot;</p>\", \"PageParagraph3Content\": \"<p>&quot;At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.&quot;</p>\"}'),
(8, 'page-base', 'fr_our_strategy', 'fr', '{\"pageTitle\": \"Our Stategy\", \"PageParagraph1Title\": \"\", \"PageParagraph2Title\": \"where do we invest?\", \"PageParagraph3Title\": \"How Do we invest?\", \"PageParagraph4Title\": \"What do we look for?\", \"PageParagraph5Title\": \"How do we create value?\", \"PageParagraphValors\": \"60% of our deals are bilateral transactions\", \"PageParagraph1Content\": \"<p>Primarily, our deals are proprietary, primary deals where we boast privileged access to deal flow due to our unrivalled connectivity with family owned businesses.</p><p>Alpha takes majority positions in our portfolio companies investing in global businesses with strong underlying fundamentals or innovative businesses disrupting their industries.&nbsp; We invest in companies with enterprise values of between &euro;100m and &euro;500m and the equity commitment typically ranges from &euro;50m to &euro;125m. Alphamay also conside larger tranctions with its own limited partners or other equity providers/houses.</p>\", \"PageParagraph2Content\": \"<p><strong>We invest mainly in the following sectors:</strong></p><ul><li><div>Industrial Manufacturing</div></li><li>Consumer &amp; lleisure</li><li><div>Fashion and Design</div></li><li><div>Service and Distribution</div></li></ul><div>We invest in business where see potential for oprational or strategic change and as such, we love to work with seasoned, ambitious and entrepreneurial management teams.</div>\", \"PageParagraph3Content\": \"<ul><li>Leveraged buy-out</li><li>Family succession / transitions</li><li>Public to Private</li><li>Spin-Off of large corporations</li><li>Majority shareholder</li><li>Minority shareholding but only under certain conditions</li></ul>\", \"PageParagraph4Content\": \"<ul><li><div>Global businesses with underlying, sustainable long term fundamentals</div></li><li><div>Innovative businesses disrupting their industry</div></li><li><div>Companies that generally enjoy strong stat&eacute;gic position on their market</div></li><li><div>Growth potential (both organic and external)</div></li><li><div>Underperformers but where we have a clear investment rationale</div></li><li><div>Seasoned, ambitious and entrepreneurial management teams</div></li></ul>\", \"PageParagraph5Content\": \"<ul><li>Accelerating organic growth</li><li>Strategic game changing acquisitions</li><li>Management reinforcement</li><li>Cash flow optimization (Balance sheet /</li><li>Financial structure / working capital)</li><li>Cost structure improvement</li></ul>\"}'),
(9, 'page-home', 'fr_testt', 'fr', '{\"pageTitle\": \"gsd\", \"PageParagraph1Title\": \"vnvjk\", \"PageParagraph1Content\": \"<p>nxvkjx</p>\"}'),
(10, 'page-responsible-investing', 'fr_testt', 'fr', '{\"pageTitle\": \"lkjlk\", \"pageTagLine\": \"\", \"PageParagraph3Title\": \"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.&nbsp;</p>\", \"PageParagraph1Content\": \"\", \"PageParagraph2Content\": \"\", \"PageParagraph3content\": \"\"}'),
(11, 'page-responsible-investing', 'fr_test', 'fr', '{\"pageTitle\": \"tets\", \"pageTagLine\": \"\", \"PageParagraph1Content\": \"<p>tet</p>\", \"PageParagraph2Content\": \"<p>ss</p>\", \"PageParagraph3content\": \"<p>ghjfs,klfnd</p>\"}'),
(12, 'page-home', 'fr_index', 'fr', '{\"pageTagLine\": \"Alpha is an independent private equity investor focused on investing in global and/or innovative businesses in France, Italy, Germany, Switzerland and Benelux with &euro;2bn under management.\", \"alphaSlideText\": \"<p>Alpha invests in businesses where we see the opportunity to create significant value and as a result real returns for our investors, by executing the Alpha strategy.</p>\", \"PageParagraph1Content\": \"<p>Alpha is an independent private equity investor focused on investing in global and/or innovative businesses in France, Italy, Germany, Switzerland and Benelux with &euro;2bn under management.</p>\", \"PageParagraph2Content\": \"<p>Primarily, our deals are proprietary, primary deals where we boast privileged access to deal flow due to our unrivalled connectivity with family owned businesses.</p><p>Alpha takes majority positions in our portfolio companies investing in global businesses with strong underlying fundamentals or innovative businesses disrupting their industries.</p>\", \"PageParagraph3Content\": \"\"}');

-- --------------------------------------------------------

--
-- Structure de la table `mib_theme`
--

CREATE TABLE `mib_theme` (
  `id` int(12) NOT NULL,
  `value` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `mib_theme`
--

INSERT INTO `mib_theme` (`id`, `value`) VALUES
(1, '{\"name\": \"test\", \"description\": \"tets\"}');

-- --------------------------------------------------------

--
-- Structure de la table `mib_urls`
--

CREATE TABLE `mib_urls` (
  `id` int(10) UNSIGNED NOT NULL,
  `url` text,
  `url_rewrited` text,
  `title` text,
  `meta_robots` text,
  `meta_description` text,
  `meta_keywords` text,
  `sitemap_priority` float NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `mib_urls`
--

INSERT INTO `mib_urls` (`id`, `url`, `url_rewrited`, `title`, `meta_robots`, `meta_description`, `meta_keywords`, `sitemap_priority`) VALUES
(1, '/', '/', '', NULL, NULL, NULL, 0),
(2, 'fr', 'fr', 'Accueil', NULL, NULL, NULL, 0),
(3, 'en', 'en', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `mib_users`
--

CREATE TABLE `mib_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `username` varchar(200) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `salt` varchar(12) DEFAULT NULL,
  `email` varchar(80) NOT NULL DEFAULT '',
  `timezone` varchar(255) DEFAULT NULL,
  `registered` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `registration_ip` varchar(39) NOT NULL DEFAULT '0.0.0.0',
  `last_visit` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `bo_perms` text,
  `admin_note` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `mib_users`
--

INSERT INTO `mib_users` (`id`, `group_id`, `username`, `password`, `salt`, `email`, `timezone`, `registered`, `registration_ip`, `last_visit`, `bo_perms`, `admin_note`) VALUES
(1, 2, 'Guest', 'Guest', NULL, 'Guest', NULL, 0, '0.0.0.0', 0, NULL, NULL),
(3, 1, 'Support 2BO', '0cf07f6d8dea6546b68a458ffd494284e0d867e4', '!ggmIaf(Q)P/', 'support@2boandco.com', NULL, 1554215623, '0.0.0.0', 1592318025, NULL, NULL),
(7, 3, 'Rui', 'Securit@tis2018', NULL, 'rui.chen1996@gmail.com', NULL, 0, '0.0.0.0', 0, NULL, NULL),
(8, 3, '', '', NULL, '', NULL, 0, '0.0.0.0', 0, NULL, NULL);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `mib_account`
--
ALTER TABLE `mib_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ix__account_matricule` (`matricule`),
  ADD KEY `ix__account_email` (`email`);

--
-- Index pour la table `mib_blog`
--
ALTER TABLE `mib_blog`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mib_configs`
--
ALTER TABLE `mib_configs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mib_found`
--
ALTER TABLE `mib_found`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mib_groups`
--
ALTER TABLE `mib_groups`
  ADD PRIMARY KEY (`g_id`);

--
-- Index pour la table `mib_lead`
--
ALTER TABLE `mib_lead`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_id` (`id`);

--
-- Index pour la table `mib_online`
--
ALTER TABLE `mib_online`
  ADD UNIQUE KEY `mib_online_user_id_ident_idx` (`user_id`,`ident`(25)),
  ADD KEY `mib_online_ident_idx` (`ident`(25)),
  ADD KEY `mib_online_logged_idx` (`logged`);

--
-- Index pour la table `mib_options`
--
ALTER TABLE `mib_options`
  ADD PRIMARY KEY (`autoid`);

--
-- Index pour la table `mib_prospective`
--
ALTER TABLE `mib_prospective`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mib_sitepage`
--
ALTER TABLE `mib_sitepage`
  ADD PRIMARY KEY (`autoid`);

--
-- Index pour la table `mib_theme`
--
ALTER TABLE `mib_theme`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mib_urls`
--
ALTER TABLE `mib_urls`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mib_users`
--
ALTER TABLE `mib_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mib_users_registered_idx` (`registered`),
  ADD KEY `mib_users_username_idx` (`username`(8));

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `mib_account`
--
ALTER TABLE `mib_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
--
-- AUTO_INCREMENT pour la table `mib_blog`
--
ALTER TABLE `mib_blog`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;
--
-- AUTO_INCREMENT pour la table `mib_configs`
--
ALTER TABLE `mib_configs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `mib_found`
--
ALTER TABLE `mib_found`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT pour la table `mib_groups`
--
ALTER TABLE `mib_groups`
  MODIFY `g_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `mib_options`
--
ALTER TABLE `mib_options`
  MODIFY `autoid` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT pour la table `mib_prospective`
--
ALTER TABLE `mib_prospective`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT pour la table `mib_sitepage`
--
ALTER TABLE `mib_sitepage`
  MODIFY `autoid` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT pour la table `mib_theme`
--
ALTER TABLE `mib_theme`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `mib_urls`
--
ALTER TABLE `mib_urls`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pour la table `mib_users`
--
ALTER TABLE `mib_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
