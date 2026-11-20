# Instalar un controlador de dominio y DNS

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Cómo un servidor se convierte en controlador de dominio (agregar el rol y promoverlo) y el DNS del que AD depende por completo. Incluye los registros SRV, cómo unir clientes al dominio, cómo un cliente encuentra un DC, el orden de resolución de nombres y los tipos de consultas DNS.

## Contenido

- [1. Preparar la instalación de AD](#1-preparar-la-instalación-de-ad)
- [2. Instalar un controlador de dominio](#2-instalar-un-controlador-de-dominio)
- [3. DNS y AD (registros SRV)](#3-dns-y-ad-registros-srv)
- [4. Unir un cliente al dominio](#4-unir-un-cliente-al-dominio)
- [5. Más conceptos de DNS](#5-más-conceptos-de-dns)
- [Resumen](#resumen)

---

## 1. Preparar la instalación de AD

El servidor que ofrece Active Directory se llama **controlador de dominio (DC)**. Antes de que un servidor pueda ser DC hay que preparar algunas cosas.

- **Forma antigua (antes de 2012)**: se usaba el comando `DCPROMO`.
- **Forma actual (2012 y posteriores)**: el Administrador del servidor (Server Manager) mejoró sus funciones de administración remota y la instalación cambió; la **promoción a DC** se hace desde el Administrador del servidor.

### Requisitos del DC

- **IP estática**: un DC debe usar una **IP estática**.
- **Configuración DNS**: el servidor donde se instala AD debe tener **su propia dirección IP** (`127.0.0.1` o su IP) como DNS preferido.
  - Hoy en día el servidor cambia automáticamente su DNS preferido a sí mismo durante la instalación de AD. (Antes no lo hacía y causaba muchos problemas.)
- **Nombre del equipo**: decide de antemano el nombre del servidor. (p. ej., `DC01`, `DC02`)

---

## 2. Instalar un controlador de dominio

Hay dos etapas: agregar el rol y después **promover** el servidor a DC.

1. **Agregar roles y características**: en el panel del Administrador del servidor, haz clic en **Agregar roles y características**.
2. **Elegir el servicio**: marca **Servicios de dominio de Active Directory**. Las herramientas de administración necesarias para AD se instalan con él.
3. **Instalar los módulos básicos**: al hacer clic en Instalar se instalan los módulos básicos de AD y las herramientas de administración.
4. **Promover a DC**: al terminar, el Administrador del servidor muestra el enlace **Promover este servidor a controlador de dominio**; al hacer clic se inicia el asistente.
5. **Elegir el nivel funcional**: salvo que haya un motivo, elige **la versión más alta**.
6. **GC/DNS**: en el primer DC el catálogo global y el servidor DNS son obligatorios, así que se seleccionan automáticamente.
7. **Contraseña de DSRM**: establece la contraseña del **modo de restauración de servicios de directorio**, que se usa cuando el DC tiene problemas.
8. **Reinicio**: revisa las rutas y haz clic en Instalar. Al terminar, el servidor se reinicia automáticamente.

### Opciones de promoción

| Opción | Cuándo elegirla |
| --- | --- |
| Agregar un nuevo bosque | Al crear el dominio **por primera vez** |
| Agregar un DC a un dominio existente | Cuando ya hay un DC y se agrega **otro más** como respaldo o para equilibrar la carga |

> **Nombre NetBIOS**: si el dominio es `korea.com`, el nombre NetBIOS será `KOREA`. Un nombre NetBIOS puede tener **hasta 15 caracteres**.

---

## 3. DNS y AD (registros SRV)

AD **no puede existir sin DNS.** AD encuentra todos los equipos a través de DNS.

### ¿Qué es DNS?

- Convierte nombres de equipos en direcciones IP.
- **FQDN** (Fully Qualified Domain Name): el nombre completo formado por el nombre de host (p. ej., `www`) y el nombre de dominio (p. ej., `koala.com`): `www.koala.com`.

### ¿Qué es un registro SRV?

- Un **registro SRV (de servicio)** es un registro cuyo nombre empieza por `_` (guion bajo). (p. ej., `_ldap._tcp.dc._msdcs.korea.com`)
- Los registros SRV contienen información como: el GC se alcanza por el puerto **3268** y la autenticación Kerberos usa el puerto **88**.
- Con varios DC se registran varios registros SRV, y el **round robin** reparte la carga cuando los clientes buscan un DC.
- Al instalar AD, los registros SRV del DC se **registran automáticamente**.

---

## 4. Unir un cliente al dominio

### Imprescindible antes de unirse: DNS

- El cliente debe tener **la dirección IP del DC** como servidor DNS; si no, no puede encontrar el DC.
- Si el DNS no apunta al DC, **la unión al dominio (AD join) falla.**

### Cómo unirse (GUI)

| Método | Detalles |
| --- | --- |
| Cambio sencillo | En Propiedades del sistema solo se escribe el nombre del dominio y se une. Para dar derechos locales a una cuenta del dominio, hay que agregarla a un grupo en Administración de equipos. |
| Id. de red | Se usa el asistente **Id. de red** de Propiedades del sistema. Une al dominio y, a la vez, puede dar a la cuenta del usuario **derechos de administrador local** en el PC cliente. |

### Cómo encuentra un cliente un DC

Cuando un cliente busca un DC para iniciar sesión o unirse al dominio, **siempre usa DNS**.

![How a client finds a DC](../images/ad-dc-locator.svg)

1. **Consultar al DNS**: el cliente pide a su servidor DNS los **registros SRV** del dominio.
2. **Obtener la lista de DC**: el DNS devuelve la lista de registros SRV (las direcciones de los DC).
3. **Ping**: el cliente hace ping a los DC de la lista para ver cuáles responden.
4. **Elegir el mejor DC**: elige el DC que responde más rápido e intenta autenticarse.

---

## 5. Más conceptos de DNS

![Name resolution order and query types](../images/ad-name-resolution.svg)

### Orden de resolución de nombres

Un equipo no pregunta primero al servidor DNS; comprueba en este orden:

1. **Nombre de host local** (él mismo)
2. **Archivo hosts** (`C:\Windows\System32\drivers\etc\hosts`)
3. **Caché DNS local**: información de IP recibida antes del DNS
4. **Consultar al servidor DNS**

### Tipos de consultas DNS

| Tipo | Descripción | Dirección |
| --- | --- | --- |
| Recursiva (Recursive) | El cliente pide al servidor DNS la **respuesta final**. | cliente ⇄ servidor |
| Iterativa (Iterative) | Para obtener la respuesta exacta, el servidor DNS **pregunta repetidamente a otros servidores**: raíz, TLD, autoritativo. | servidor ⇄ servidor |

---

## Resumen

- Un DC necesita **IP estática**, **DNS que apunte a sí mismo** y un **nombre de equipo decidido de antemano**. Desde 2012 la promoción se hace en el **Administrador del servidor** en lugar de `DCPROMO`.
- Instalación: **agregar el rol AD DS → promover** (bosque nuevo / agregar a dominio existente) → nivel funcional → GC/DNS → **contraseña de DSRM** → reinicio. Los nombres NetBIOS tienen hasta **15 caracteres**.
- AD **no funciona sin DNS.** Los **registros SRV** que empiezan por `_` se registran automáticamente para encontrar los DC, el GC (3268) y Kerberos (88).
- Un cliente debe **usar el DC como DNS** para unirse al dominio. El asistente **Id. de red** también concede derechos de administrador local.
- Encontrar un DC: **consulta SRV → lista de DC → ping → DC más rápido**.
- La resolución de nombres sigue **nombre de host local → archivo hosts → caché → servidor DNS**; las consultas son **recursivas** (cliente↔servidor) o **iterativas** (servidor↔servidor).
