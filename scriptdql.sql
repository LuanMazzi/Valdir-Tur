USE bdValdirTur;

SET @dataInicio = DATE_FORMAT(CURDATE(), '%Y-%m-01');
SET @dataFim = CURDATE();

SELECT venda.*, cliente.tipoCliente, cliente.nome, cliente.sobrenome, cliente.razaoSocial
FROM tbVenda venda
LEFT JOIN tbCliente cliente ON venda.idCliente = cliente.idCliente
WHERE venda.dataVenda BETWEEN @dataInicio AND @dataFim
ORDER BY venda.dataVenda DESC;

SELECT fretamento.*, cliente.tipoCliente, cliente.nome, cliente.sobrenome, cliente.razaoSocial
FROM tbFretamento fretamento
LEFT JOIN tbCliente cliente ON fretamento.idCliente = cliente.idCliente
WHERE DATE(fretamento.dataHoraSaida) BETWEEN @dataInicio AND @dataFim
ORDER BY fretamento.dataHoraSaida DESC;

SELECT COUNT(*) AS total, COALESCE(SUM(valorRecebido), 0) AS soma
FROM tbVenda
WHERE dataVenda BETWEEN @dataInicio AND @dataFim;

SELECT COUNT(*) AS total, COALESCE(SUM(preco), 0) AS soma
FROM tbFretamento
WHERE DATE(dataHoraSaida) BETWEEN @dataInicio AND @dataFim;
