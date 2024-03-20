
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


Tabela etiquetas

CREATE TABLE etiquetas(
	codigo_etiquetas INT NOT NULL AUTO_INCREMENT,
    cor varchar(45) NOT NULL,
    tipo varchar(60) NOT NULL,
    
    CONSTRAINT pk_etiquetas PRIMARY KEY (codigo_etiquetas)
)

Tabela Passo

CREATE TABLE passo(
	codigo_passo INT NOT NULL AUTO_INCREMENT,
    titulo varchar(45) NOT NULL,
    descricao varchar(60) NOT NULL,
    codigo_check INT NOT NULL,
    
    CONSTRAINT pk_passo PRIMARY KEY (codigo_passo),
    CONSTRAINT fk_check FOREIGN KEY (codigo_check) REFERENCES checklist (codigo_check)
)

Tabela Contrato

CREATE TABLE contrato (
	codigo_contrato INT NOT NULL AUTO_INCREMENT,
    tipo varchar(45) NOT NULL,
    titulo varchar(45) NOT NULL,
    referencia varchar(45) NOT NULL,
    valor_negociado varchar(11) NOT NULL,
    honorarios varchar(11) NOT NULL,
    obs varchar(250),
    dt_criacao DATETIME NOT NULL,
    dt_atualizacao DATETIME NOT NULL,
    status_contrato varchar(45) NOT NULL,
    desc_status varchar(100) NOT NULL,
    imoveis_codigo INT NOT NULL,
    etiquetas_codigo INT NOT NULL,
    checklist_codigo INT NOT NULL,
    
    CONSTRAINT pk_contrato PRIMARY KEY (codigo_contrato),
    CONSTRAINT fk_imoveis FOREIGN KEY (imoveis_codigo) REFERENCES imoveis(codigo_imovel),
	CONSTRAINT fk_etiquetas FOREIGN KEY (etiquetas_codigo) REFERENCES etiquetas (codigo_etiquetas),
    CONSTRAINT fk_checklist FOREIGN KEY (checklist_codigo) REFERENCES checklist(codigo_check)
)

Grupo compradores

CREATE TABLE grupo_compradores(
    codigo_contrato INT NOT NULL,
    codigo_clientes INT NOT NULL,
    porcentagem INT NOT NULL,
    
    CONSTRAINT pk_compradores PRIMARY KEY (codigo_contrato, codigo_clientes),
    CONSTRAINT fk_contrato FOREIGN KEY (codigo_contrato) REFERENCES contrato(codigo_contrato),
    CONSTRAINT fk_clientes FOREIGN KEY (codigo_clientes) REFERENCES clientes(codigo_clientes)
)

Tabela documentos

CREATE TABLE documentos (
	codigo_documento INT NOT NULL,
    dt_criacao DATETIME NOT NULL,
    dt_atualizacao DATETIME NOT NULL,
    dt_assinatura DATETIME,
	responsavel_assinatura Varchar(100) NOT NULL,
    path varchar(150) NOT NULL,
    descricao varchar(100) NOT NULL,
    codigo_contrato INT NOT NULL,
    codigo_clientes INT NOT NULL,
    
    CONSTRAINT pk_documentos PRIMARY KEY (codigo_documento),
    CONSTRAINT fk_codigo_contrato FOREIGN KEY (codigo_contrato) REFERENCES contrato(codigo_contrato),
    CONSTRAINT fk_codigo_clientes FOREIGN KEY (codigo_clientes) REFERENCES clientes(codigo_clientes)
)