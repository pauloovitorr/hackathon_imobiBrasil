
Tabela Clientes

CREATE TABLE clientes(
	codigo_clientes INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    rg VARCHAR (14) NOT NULL,
    telefone VARCHAR (14) NOT NULL,
    email VARCHAR (80) NOT NULL,
    perfil VARCHAR (70) NOT NULL,
    dt_nascimento DATE,
    img VARCHAR (150),
    CONSTRAINT pk_clientes PRIMARY KEY (codigo_clientes)
)


Tabela Corretores

CREATE TABLE corretor(
	codigo_corretor INT NOT NULL AUTO_INCREMENT,
    creci VARCHAR(6) NOT NULL,
    codigo_equipe INT,
	id_cliente_corretor INT NOT NULL,
    
    CONSTRAINT pk_corretor PRIMARY KEY (codigo_corretor),
    CONSTRAINT fk_cliente FOREIGN KEY (id_cliente_corretor) REFERENCES clientes (codigo_clientes),
    CONSTRAINT fk_equipe FOREIGN KEY (codigo_equipe) REFERENCES equipe (codigo_equipe)
)

Tabela Equipe

CREATE TABLE equipe (
	codigo_equipe INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,

    CONSTRAINT pk_equipe PRIMARY KEY (codigo_equipe)    
)


Tabela imóveis

CREATE TABLE imoveis(
	codigo_imovel INT NOT NULL AUTO_INCREMENT,
    cep VARCHAR(9) NOT NULL,
    rua VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    num_casa VARCHAR(6) NOT NULL,
    dt_criacao DATETIME,
    dt_atualizacao DATETIME,
    img VARCHAR(100),
    obs VARCHAR(200),
    cod_corretor INT,
    cod_proprietario INT,
    
    CONSTRAINT pk_imovel PRIMARY KEY (codigo_imovel),
    CONSTRAINT fk_corretor FOREIGN KEY (cod_corretor) REFERENCES corretor (codigo_corretor),
    CONSTRAINT fk_proprietario FOREIGN KEY (cod_proprietario) REFERENCES clientes (codigo_clientes)
)


tabela etiquetas

CREATE TABLE etiquetas(
	codigo_etiquetas INT NOT NULL AUTO_INCREMENT,
    cor varchar(45) NOT NULL,
    tipo varchar(60) NOT NULL,
    
    CONSTRAINT pk_etiquetas PRIMARY KEY (codigo_etiquetas)
)
