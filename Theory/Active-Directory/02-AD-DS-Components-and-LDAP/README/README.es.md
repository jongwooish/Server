# Componentes de AD DS y LDAP

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Los protocolos que AD usa para autenticar (Kerberos) y para consultar información (LDAP), y los componentes físicos y lógicos que forman AD. Después, los conceptos de dominio, OU, DN y bosque, con diagramas.

## Contenido

- [1. Términos y protocolos básicos](#1-términos-y-protocolos-básicos)
- [2. Componentes de AD](#2-componentes-de-ad)
- [3. Dominios de AD DS](#3-dominios-de-ad-ds)
- [4. OU y nombres distintivos](#4-ou-y-nombres-distintivos)
- [5. LDAP y herramientas de consulta](#5-ldap-y-herramientas-de-consulta)
- [6. Bosques de AD DS](#6-bosques-de-ad-ds)
- [Resumen](#resumen)

---

## 1. Términos y protocolos básicos

### Términos

| Inglés | Significado |
| --- | --- |
| Attribute | Atributo |
| Object | Objeto |
| department | Atributo del nombre del departamento |
| displayName | Atributo del nombre para mostrar |

### Kerberos para autenticar, LDAP para consultar

- Active Directory usa el protocolo **Kerberos** para la autenticación, en el puerto **88**.
- AD no solo sirve para autenticar; también se puede usar como **base de datos de información**.
- Para **consultar** algo se usa el protocolo **LDAP**, en el puerto **389**.
- En resumen, AD es una base de datos: sus datos se usan para **autenticar** y también se puede obtener información con **consultas**.

---

## 2. Componentes de AD

AD tiene **componentes físicos** que se pueden ver y **componentes lógicos** que son conceptuales. El servidor que se encarga de la autenticación en AD se llama **controlador de dominio (Domain Controller, DC)**.

![AD DS components](../images/ad-components.svg)

### Componentes físicos

- **Data store**: los archivos de base de datos visibles en el DC.
- **Domain controllers**: los servidores que se encargan de la autenticación.
- **Global catalog server**: un DC con un papel especial se llama GC.
- **RODC**: un controlador de dominio de solo lectura.

### Componentes lógicos

- **Partitions**: la base de datos de AD está dividida internamente por uso; cada parte es una partición.
- **Schema**: las definiciones de los objetos y atributos (Attribute).
- **Domains**: un límite de administración y autenticación.
- **Domain trees**: un árbol formado por varios dominios enlazados.
- **Forests**: un conjunto de varios árboles de dominios.
- **Sites**: representan una división de la red.
- **OUs**: unidades organizativas: un límite dividido según alguna necesidad.

---

## 3. Dominios de AD DS

- Un dominio es **el alcance máximo que puede autenticar un controlador de dominio**.
- Para crear un dominio hace falta **al menos un controlador de dominio**.
- El controlador de dominio guarda la información del dominio **objeto por objeto**.
- AD recomienda tener **dos o más** controladores de dominio. (p. ej., equilibrio de carga, tolerancia a fallos)
- Un administrador del dominio tiene **todos los derechos** dentro del dominio.
- El dominio es **el límite más pequeño** de AD.

---

## 4. OU y nombres distintivos

### OU (Organizational Unit)

Una OU es la forma de **dividir lógicamente un dominio en partes**.

![OUs inside a domain](../images/ad-ou-structure.svg)

- Sirve para subdividir el dominio y facilitar la administración.
- También es **la unidad para desplegar la directiva de grupo (GPO)**.

### Nombre distintivo (Distinguished Name, DN)

El nombre que **identifica de forma única un objeto** en AD es su nombre distintivo. Se escribe desde el objeto de abajo hasta el dominio de arriba.

- `OU=Sales,DC=KOALRA,DC=COM`: la OU Sales del dominio KOALRA.COM
- `CN=Koalra,OU=Marketing,DC=KOALRA,DC=COM`: el objeto Koalra dentro de la OU Marketing

---

## 5. LDAP y herramientas de consulta

AD es una base de datos. **LDAP (Lightweight Directory Access Protocol)** es un protocolo de servicio de directorio para gestionar y consultar de forma centralizada y eficiente información como usuarios, grupos y dispositivos en la red.

AD admite consultas de forma predeterminada; estas herramientas permiten consultarlo:

- **Herramientas de administración de Active Directory** (Usuarios y equipos, etc.)
- **LDP** (`ldp.exe`)
- **Editor ADSI** (`ADSIEDIT.MSC`)

---

## 6. Bosques de AD DS

AD puede tener varios dominios, y esos dominios enlazados entre sí forman un **bosque (Forest)**.

![Domains, trees and a forest](../images/ad-forest.svg)

- Un bosque es **un conjunto de dominios con la misma estructura de atributos y objetos**.
- Tener la misma estructura se describe como **compartir el mismo esquema (Schema)**.
- Los dominios unidos en un mismo espacio de nombres contiguo (p. ej., `sales.koalra.com` bajo `koalra.com`) forman un **árbol de dominios**, y varios árboles forman un bosque.

---

## Resumen

- AD es una base de datos que autentica con **Kerberos (88)** y se consulta con **LDAP (389)**.
- Componentes físicos: **data store, DC, GC, RODC**. Componentes lógicos: **particiones, esquema, dominios, árboles de dominios, bosques, sitios, OU**.
- Un **dominio** es el alcance máximo que puede autenticar un DC y el límite más pequeño de AD; se recomiendan dos o más DC.
- Una **OU** divide lógicamente un dominio y es **la unidad a la que se aplica la directiva de grupo**.
- Un **DN** identifica de forma única un objeto con el formato `CN=…,OU=…,DC=…`.
- Un **bosque** es un conjunto de dominios (árboles) que comparten el mismo esquema.
