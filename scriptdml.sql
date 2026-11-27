USE bdValdirTur;

INSERT INTO tbVeiculo (tipoVeiculo, nomeIdentificacao, numeracao, capacidadeTotal, capacidadePrimeiroAndar, capacidadeSegundoAndar, midia, descricao, tags, ano, placa, tipoLeito, leitoPrimeiroAndar, leitoSegundoAndar, status) VALUES ();

INSERT INTO tbFuncionario (nome, sobrenome, CPF, RG, dataNascimento, telefone, email, funcao, senha, cidade, UF, CEP, logradouro, bairro, numero, status) VALUES ();

INSERT INTO tbCliente (tipoCliente, nome, sobrenome, dataNascimento, CPF, RG, razaoSocial, nomeFantasia, nomeResponsavel, CNPJ, email, telefone, cidade, UF, CEP, logradouro, bairro, numero, status) VALUES ();

INSERT INTO tbPacote (nomePacote, descricaoCurta, descricaoLonga, destino, locaisEmbarque, dataHoraSaida, dataHoraRetorno, preco, qtdParcelas, juros, duracaoViagem, vagasDisponiveis, pacoteParceiro, nomeParceiro, midia, status) VALUES ();

INSERT INTO tbFretamento (idVeiculo, idCliente, idFuncionario, cidadeOrigem, locaisEmbarque, dataHoraSaida, destino, dataHoraRetorno, valorCombustivel, qtdPassageiros, qtdKm, valorKm, consumoCombustivel, preco, valorSinal, status) VALUES ();

INSERT INTO tbVenda (dataVenda, idFuncionario, idCliente, idPacote, idFretamento, formaRecebimento, dataRecebimento, valorRecebido, qtdParcelas, juros, status) VALUES ();
