CREATE DATABASE bdValdirTur;
USE bdValdirTur;

CREATE TABLE tbVeiculo (
    idVeiculo INT PRIMARY KEY AUTO_INCREMENT,
    tipoVeiculo VARCHAR(20) NOT NULL,
    nomeIdentificacao VARCHAR(250) NOT NULL,
    numeracao INT NOT NULL,
    capacidadeTotal INT NOT NULL,
    capacidadePrimeiroAndar INT NULL,
    capacidadeSegundoAndar INT NULL,
    midia VARCHAR(500),
    descricao VARCHAR(350),
    tags VARCHAR(400),
    ano INT NOT NULL,
    placa VARCHAR(7) NOT NULL UNIQUE,
    tipoLeito VARCHAR(200) NULL,
    leitoPrimeiroAndar VARCHAR(200) NULL,
    leitoSegundoAndar VARCHAR(200) NULL,
    status VARCHAR(100) NOT NULL
);


CREATE TABLE tbFuncionario (
    idFuncionario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(250) NOT NULL,
    sobrenome VARCHAR(250) NOT NULL,
    CPF VARCHAR(14) NOT NULL UNIQUE,
    RG VARCHAR(14) NOT NULL,
    dataNascimento DATE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(150) NOT NULL,
    funcao VARCHAR(100) NOT NULL,
	senha VARCHAR(150) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    UF VARCHAR(2) NOT NULL,
    CEP VARCHAR(9) NOT NULL,
    logradouro VARCHAR(150) NOT NULL,
    bairro VARCHAR(150) NOT NULL,
    numero INT NOT NULL,
    status VARCHAR(100) NOT NULL
);

CREATE TABLE tbCliente (
    idCliente INT PRIMARY KEY AUTO_INCREMENT,
    tipoCliente VARCHAR(100) NOT NULL,

    -- Pessoa Fisica
    nome VARCHAR(250) NULL,
    sobrenome VARCHAR(250) NULL,
    dataNascimento DATE NULL,
    CPF VARCHAR(14) NULL UNIQUE,
    RG VARCHAR(14) NULL UNIQUE,

    -- Pessoa Juridica
    razaoSocial VARCHAR(150) NULL,
    nomeFantasia VARCHAR(150) NULL,
    nomeResponsavel VARCHAR(150) NULL,
    CNPJ VARCHAR(18) NULL UNIQUE,

    -- Comuns a PF e PJ
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    UF VARCHAR(2) NOT NULL,
    CEP VARCHAR(9) NOT NULL,
    logradouro VARCHAR(150) NOT NULL,
    bairro VARCHAR(150) NOT NULL,
    numero INT NOT NULL,
    status VARCHAR(100) NOT NULL
);

CREATE TABLE tbPacote (
    idPacote INT PRIMARY KEY AUTO_INCREMENT,
    nomePacote VARCHAR(150) NOT NULL,
	descricaoCurta VARCHAR(100),
    descricaoLonga TEXT,
	destino VARCHAR(150) NOT NULL,
	locaisEmbarque VARCHAR(150) NOT NULL,
	dataHoraSaida DATETIME NOT NULL,
    dataHoraRetorno DATETIME NOT NULL,
    preco DOUBLE NOT NULL,
    qtdParcelas INT,
    juros INT,
    duracaoViagem TIME NOT NULL,
    vagasDisponiveis INT NOT NULL,
    pacoteParceiro VARCHAR (100) NOT NULL,
    midia VARCHAR(500),
    status VARCHAR(100) NOT NULL
);

CREATE TABLE tbFretamento (
    idFretamento INT PRIMARY KEY AUTO_INCREMENT,
    idVeiculo INT,
    idCliente INT,
    idFuncionario INT,
    cidadeOrigem VARCHAR(150) NOT NULL,
    locaisEmbarque VARCHAR(250) NOT NULL,
    dataHoraSaida DATETIME NOT NULL,
    destino VARCHAR(150) NOT NULL,
    dataHoraRetorno DATETIME NOT NULL,
    valorCombustivel DOUBLE NOT NULL,
    qtdPassageiros INT NOT NULL,
    qtdKm DOUBLE NOT NULL,
    consumoCombustivel DOUBLE NOT NULL,
    preco DOUBLE NOT NULL,
    status VARCHAR(100) NOT NULL,
    FOREIGN KEY (idVeiculo)
        REFERENCES tbVeiculo (idVeiculo),
    FOREIGN KEY (idCliente)
        REFERENCES tbCliente (idCliente),
    FOREIGN KEY (idFuncionario)
        REFERENCES tbFuncionario (idFuncionario)
);

CREATE TABLE tbVenda (
    idVenda INT PRIMARY KEY AUTO_INCREMENT,
    dataVenda DATE NOT NULL,
    idFuncionario INT,
    idCliente INT,
    idPacote INT,
    idFretamento INT,
    formaRecebimento VARCHAR(150) NOT NULL,
    dataRecebimento DATE NOT NULL,
    valorRecebido DOUBLE NOT NULL,
    qtdParcelas INT,
    juros INT,
    status VARCHAR(100) NOT NULL,
    FOREIGN KEY (idPacote)
        REFERENCES tbPacote (idPacote),
    FOREIGN KEY (idCliente)
        REFERENCES tbCliente (idCliente),
    FOREIGN KEY (idFuncionario)
        REFERENCES tbFuncionario (idFuncionario),
	FOREIGN KEY (idFretamento)
        REFERENCES tbFretamento (idFretamento)
);



