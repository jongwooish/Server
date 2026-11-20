# Controladores de dominio, esquema y FSMO

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Un vistazo al interior del controlador de dominio (DC) que ofrece AD. Esta página trata el esquema y la estructura de objetos, la base de datos, el servicio de autenticación y los archivos de directiva de grupo que forman un DC, las particiones de la base de datos, el catálogo global y los roles FSMO de todo el bosque.

## Contenido

- [1. ¿Qué es un controlador de dominio?](#1-qué-es-un-controlador-de-dominio)
- [2. Esquema y estructura de objetos](#2-esquema-y-estructura-de-objetos)
- [3. Componentes de un DC](#3-componentes-de-un-dc)
- [4. Particiones de la base de datos de AD](#4-particiones-de-la-base-de-datos-de-ad)
- [5. Catálogo global (GC)](#5-catálogo-global-gc)
- [6. Roles FSMO (maestros de operaciones)](#6-roles-fsmo-maestros-de-operaciones)
- [Resumen](#resumen)

---

## 1. ¿Qué es un controlador de dominio?

- El equipo que ofrece todo en AD se llama **DC (Domain Controller)**. Los únicos archivos que realmente se ven en un DC son los de la **base de datos**.
- La autenticación usa el protocolo **Kerberos**, y el servicio que la gestiona se llama **servicio KDC**.
- Conviene tener **dos o más** controladores de dominio.

---

## 2. Esquema y estructura de objetos

Active Directory se construye sobre un esqueleto llamado **esquema (Schema)**.

- **Objetos y atributos**: AD está formado por objetos, y cada objeto tiene atributos.
- **Esquema**: el esqueleto básico de esos objetos y atributos, es decir, **la definición de diseño de la plantilla**, se llama esquema.
- **Misma estructura**: **todo un bosque usa el mismo esquema**.
- **Principio y fin de AD**: toda estructura de AD empieza y termina con el esquema.

---

## 3. Componentes de un DC

Un controlador de dominio es el servidor que ofrece AD; contiene el servicio de autenticación y la base de datos.

![Inside a domain controller](../images/ad-dc-components.svg)

| Componente | Detalles |
| --- | --- |
| Archivo de base de datos de AD | Los datos de AD se guardan como `NTDS.dit` en la carpeta `C:\Windows\NTDS`. |
| Servicio de autenticación | Usa el protocolo Kerberos; el servicio que lo gestiona es el **KDC** (Key Distribution Center, centro de distribución de claves). |
| Archivos de directiva de grupo | Los archivos para procesar la directiva de grupo se guardan en la carpeta **SYSVOL**. |

---

## 4. Particiones de la base de datos de AD

La base de datos de AD (`NTDS.dit`) se guarda en varias partes, y cada partición tiene un **ámbito de replicación** distinto.

| Partición | Qué guarda | Ámbito |
| --- | --- | --- |
| Schema | Definiciones de la estructura básica de objetos y atributos | Todo el bosque |
| Configuration | Configuración que debe ser idéntica en todo el bosque | Todo el bosque |
| Domain | Datos propios del dominio, como usuarios, grupos y equipos | Ese dominio |
| Application | Espacio reservado para programas que usan AD (p. ej., DNS) | DC elegidos |

---

## 5. Catálogo global (GC)

El GC es el servidor que se encarga de las **búsquedas y consultas de libreta de direcciones en todo el bosque**.

![Global catalog and FSMO roles](../images/ad-gc-fsmo.svg)

- Se usa para encontrar **cuentas de otros dominios** del mismo bosque o para buscar información en todo el bosque.
- Las consultas al GC usan el puerto **3268**, no el puerto LDAP normal 389.
- Para evitar tráfico de red innecesario, se recomienda tener **al menos un GC por sitio**.

---

## 6. Roles FSMO (maestros de operaciones)

AD usa **replicación multimaestro**, pero las operaciones importantes que nunca deben entrar en conflicto se asignan a un **único maestro (Single Master)**. Estos roles se llaman **maestros de operaciones (FSMO, Flexible Single Master Operations)**.

### Maestros de operaciones de todo el bosque

Cada uno de estos roles lo tiene **un solo DC por bosque**.

- **Maestro de esquema (Schema Master)**: cambia, modifica y amplía el esquema de todo el bosque.
- **Maestro de nombres de dominio (Domain Naming Master)**: gestiona la estructura de dominios del bosque. (agregar y quitar dominios)

---

## Resumen

- Un **DC** es el servidor que ofrece AD; la autenticación Kerberos la gestiona el **servicio KDC**. Mantén dos o más DC.
- El **esquema** define objetos y atributos, y **todo el bosque usa el mismo esquema**.
- El núcleo de un DC es **`C:\Windows\NTDS\NTDS.dit`** (base de datos), el **KDC** (autenticación) y **SYSVOL** (archivos de directiva de grupo).
- Las particiones de la base de datos son **Schema y Configuration** (todo el bosque), **Domain** (un dominio) y **Application** (p. ej., DNS).
- El **GC** gestiona las búsquedas en todo el bosque por el puerto **3268**; pon al menos uno por sitio.
- Las operaciones que no deben entrar en conflicto se asignan a un DC como roles **FSMO**. Los de todo el bosque son el **maestro de esquema** y el **maestro de nombres de dominio**.
