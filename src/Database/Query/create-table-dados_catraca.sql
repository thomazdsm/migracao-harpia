SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Estrutura da tabela `dados_catraca`
--

CREATE TABLE `dados_catraca` (
    `id` int(10) UNSIGNED NOT NULL,
    `tipo` varchar(255) NOT NULL,
    `dataHora` varchar(255) NOT NULL,
    `codCatraca` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `dados_catraca`
--
ALTER TABLE `dados_catraca`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `dados_catraca`
--
ALTER TABLE `dados_catraca`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10791;