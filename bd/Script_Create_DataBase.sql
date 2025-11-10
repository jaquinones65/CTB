-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema colombiatierrabendita
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema colombiatierrabendita
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `colombiatierrabendita` DEFAULT CHARACTER SET utf8mb3 ;
USE `colombiatierrabendita` ;

-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`roles` (
  `id_rol` INT NOT NULL AUTO_INCREMENT,
  `fecha_creacion` DATE NOT NULL,
  `nombre` VARCHAR(45) NOT NULL,
  `descripcion` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE INDEX `id_rol_UNIQUE` (`id_rol` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`aliados`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`aliados` (
  `id_aliados` INT NOT NULL AUTO_INCREMENT,
  `nombre_aliado` VARCHAR(45) NOT NULL,
  `user` VARCHAR(45) NOT NULL,
  `contrasena` VARCHAR(45) NOT NULL,
  `id_rol` INT NOT NULL,
  PRIMARY KEY (`id_aliados`, `id_rol`),
  UNIQUE INDEX `id_aliados_UNIQUE` (`id_aliados` ASC) VISIBLE,
  INDEX `fk_Aliados_roles1_idx` (`id_rol` ASC) VISIBLE,
  CONSTRAINT `fk_Aliados_roles1`
    FOREIGN KEY (`id_rol`)
    REFERENCES `colombiatierrabendita`.`roles` (`id_rol`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`departamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`departamentos` (
  `id_departamento` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_departamento`),
  UNIQUE INDEX `id_departamento_UNIQUE` (`id_departamento` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 34
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`ciudades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`ciudades` (
  `id_ciudad` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `id_departamento` INT NOT NULL,
  PRIMARY KEY (`id_ciudad`, `id_departamento`),
  INDEX `fk_Ciudades_Departamentos_idx` (`id_departamento` ASC) VISIBLE,
  CONSTRAINT `fk_Ciudades_Departamentos`
    FOREIGN KEY (`id_departamento`)
    REFERENCES `colombiatierrabendita`.`departamentos` (`id_departamento`))
ENGINE = InnoDB
AUTO_INCREMENT = 1123
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`cursos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`cursos` (
  `id_curso` INT NOT NULL AUTO_INCREMENT,
  `fecha_creacion` DATE NOT NULL,
  `nombre_curso` VARCHAR(100) NOT NULL,
  `activo` CHAR(1) NOT NULL,
  `descripcion` VARCHAR(250) NOT NULL,
  PRIMARY KEY (`id_curso`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`evangelistas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`evangelistas` (
  `id_evangelistas` INT NOT NULL AUTO_INCREMENT,
  `fecha_registro` DATE NOT NULL,
  `nombre_completo` VARCHAR(50) NOT NULL,
  `documento` VARCHAR(15) NOT NULL,
  `telefono` VARCHAR(15) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `numero_almas` INT NOT NULL,
  `acepta` CHAR(1) NOT NULL,
  `id_rol` INT NOT NULL,
  `id_ciudad` INT NOT NULL,
  `id_departamento` INT NOT NULL,
  PRIMARY KEY (`id_evangelistas`, `id_rol`, `id_ciudad`, `id_departamento`),
  UNIQUE INDEX `documento_UNIQUE` (`documento` ASC) VISIBLE,
  INDEX `fk_Evangelistas_Roles1_idx` (`id_rol` ASC) VISIBLE,
  INDEX `fk_evangelistas_ciudades1_idx` (`id_ciudad` ASC, `id_departamento` ASC) VISIBLE,
  CONSTRAINT `fk_evangelistas_ciudades1`
    FOREIGN KEY (`id_ciudad` , `id_departamento`)
    REFERENCES `colombiatierrabendita`.`ciudades` (`id_ciudad` , `id_departamento`),
  CONSTRAINT `fk_Evangelistas_Roles1`
    FOREIGN KEY (`id_rol`)
    REFERENCES `colombiatierrabendita`.`roles` (`id_rol`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`evangelizados`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`evangelizados` (
  `id_evangelizado` INT NOT NULL AUTO_INCREMENT,
  `fecha_creacion` DATE NOT NULL,
  `nombre_completo` VARCHAR(50) NOT NULL,
  `documento` VARCHAR(15) NOT NULL,
  `telefono` VARCHAR(15) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `id_rol` INT NOT NULL,
  `id_curso` INT NULL DEFAULT NULL,
  `Evangelistas_documento` VARCHAR(12) NOT NULL,
  `id_ciudad` INT NOT NULL,
  `id_departamento` INT NOT NULL,
  PRIMARY KEY (`id_evangelizado`),
  UNIQUE INDEX `documento_UNIQUE` (`documento` ASC) VISIBLE,
  INDEX `fk_Evangelizados_Roles1_idx` (`id_rol` ASC) VISIBLE,
  INDEX `fk_Evangelizados_Cursos1_idx` (`id_curso` ASC) VISIBLE,
  INDEX `fk_Evangelizados_Evangelistas1_idx` (`Evangelistas_documento` ASC) VISIBLE,
  INDEX `fk_evangelizados_ciudades1_idx` (`id_ciudad` ASC, `id_departamento` ASC) VISIBLE,
  CONSTRAINT `fk_evangelizados_ciudades1`
    FOREIGN KEY (`id_ciudad` , `id_departamento`)
    REFERENCES `colombiatierrabendita`.`ciudades` (`id_ciudad` , `id_departamento`),
  CONSTRAINT `fk_Evangelizados_Cursos1`
    FOREIGN KEY (`id_curso`)
    REFERENCES `colombiatierrabendita`.`cursos` (`id_curso`),
  CONSTRAINT `fk_Evangelizados_Evangelistas1`
    FOREIGN KEY (`Evangelistas_documento`)
    REFERENCES `colombiatierrabendita`.`evangelistas` (`documento`),
  CONSTRAINT `fk_Evangelizados_Roles1`
    FOREIGN KEY (`id_rol`)
    REFERENCES `colombiatierrabendita`.`roles` (`id_rol`))
ENGINE = InnoDB
AUTO_INCREMENT = 15
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`evangelizados_cursos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`evangelizados_cursos` (
  `id_evangelizado_curso` INT NOT NULL AUTO_INCREMENT,
  `id_evangelizado` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `fecha_inicio` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` DATETIME NULL DEFAULT NULL,
  `estado` ENUM('EN_CURSO', 'FINALIZADO') NULL DEFAULT 'EN_CURSO',
  PRIMARY KEY (`id_evangelizado_curso`),
  INDEX `id_evangelizado` (`id_evangelizado` ASC) VISIBLE,
  INDEX `id_curso` (`id_curso` ASC) VISIBLE,
  CONSTRAINT `evangelizados_cursos_ibfk_1`
    FOREIGN KEY (`id_evangelizado`)
    REFERENCES `colombiatierrabendita`.`evangelizados` (`id_evangelizado`),
  CONSTRAINT `evangelizados_cursos_ibfk_2`
    FOREIGN KEY (`id_curso`)
    REFERENCES `colombiatierrabendita`.`cursos` (`id_curso`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `colombiatierrabendita`.`pastores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colombiatierrabendita`.`pastores` (
  `id_pastor` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL,
  `contrasenna` VARCHAR(45) NOT NULL,
  `id_rol` INT NOT NULL,
  PRIMARY KEY (`id_pastor`, `id_rol`),
  UNIQUE INDEX `id_pastor_UNIQUE` (`id_pastor` ASC) VISIBLE,
  INDEX `fk_Pastores_Roles1_idx` (`id_rol` ASC) VISIBLE,
  CONSTRAINT `fk_Pastores_Roles1`
    FOREIGN KEY (`id_rol`)
    REFERENCES `colombiatierrabendita`.`roles` (`id_rol`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb3;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
