create TABLE mib_account_source (
    id     int(11)  PRIMARY KEY     NOT NULL AUTO_INCREMENT,
    matricule varchar(100)  NOT NULL,
    lastname   varchar(255)     NOT NULL,
    firstname   varchar(255)     NULL,
    company varchar(255)     NULL,
    place varchar(255)     NULL,
    birthdate   datetime     NOT NULL,
  INDEX ix__account_matricule (matricule)
);


create TABLE mib_account (
  id     int(11)  PRIMARY KEY    NOT NULL AUTO_INCREMENT,
  matricule varchar(100)  NOT NULL,
  lastname   varchar(255)     NOT NULL,
  firstname   varchar(255)     NULL,
  company varchar(255)     NULL,
  place varchar(255)     NULL,
  birthdate   datetime    NULL,
  email  varchar(255)  NOT NULL,
  password  varchar(255)  NOT NULL,
  news int(1) NOT NULL DEFAULT 0 ,
  mail int(1) NOT NULL DEFAULT 0 ,
  dateCreate   datetime     NOT NULL,
  INDEX ix__account_matricule (matricule),
  INDEX ix__account_email (email)
);

CREATE TABLE mib_account_code (
  id     int(11)    PRIMARY KEY   NOT NULL AUTO_INCREMENT,
  type   varchar(20)  NOT NULL,
  compte varchar(10)  NOT NULL,
  code   varchar(20)  NOT NULL,
  date   datetime     NOT NULL,
  INDEX ix_account_code_code (code),
  INDEX ix_account_code_date (date),
  INDEX ix_account_code_compte (compte)
);
