# Cuentas de equipo, replicación, sitios y RODC

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Cómo se comportan las cuentas de equipo cuando una máquina se une al dominio, y consejos para administrar cuentas de AD. Después, cómo se replican los DC entre sí, los sitios y servicios para redes lentas, y el controlador de dominio de solo lectura (RODC) para ubicaciones poco seguras.

## Contenido

- [1. Cuentas de equipo y AD](#1-cuentas-de-equipo-y-ad)
- [2. Consejos para administrar cuentas de AD](#2-consejos-para-administrar-cuentas-de-ad)
- [3. Replicación de AD](#3-replicación-de-ad)
- [4. Sitios y servicios](#4-sitios-y-servicios)
- [5. RODC](#5-rodc)
- [Resumen](#resumen)

---

## 1. Cuentas de equipo y AD

- Cuando un equipo se une al dominio, se crea automáticamente una **cuenta de equipo**.
- La cuenta de equipo también tiene **contraseña**, que cambia automáticamente **cada 30 días** para la comunicación entre el equipo y el DC.
- El equipo y el DC usan esa contraseña para establecer un **canal seguro (Secure Channel)** y comunicarse.
- Si un equipo no puede contactar con un DC durante **más de 30 días**, haz que use VPN o DirectAccess, o aumenta en la directiva de grupo la **vigencia máxima** de la contraseña de la cuenta de equipo.

---

## 2. Consejos para administrar cuentas de AD

- **Dónde se aplica la directiva de grupo**: la directiva de grupo se puede vincular a las OU (unidades organizativas), pero no a los **contenedores Users** o **Computers**, donde se crean las cuentas por defecto.
- **Cambiar la ubicación predeterminada**: para que las cuentas nuevas se creen en una OU, usa los comandos **`redirusr`** (usuarios) y **`redircmp`** (equipos).
- **Herramienta masiva (LDIFDE)**: para **importar** de forma masiva propiedades de usuario como el departamento o el teléfono, o para **exportar** datos de AD, usa **`LDIFDE`**.

```cmd
redirusr "OU=NewUsers,DC=korea,DC=com"
redircmp "OU=NewComputers,DC=korea,DC=com"
```

---

## 3. Replicación de AD

### Con varios DC

- Cuando hay varios DC, **la replicación es continua** para que todos tengan los mismos datos.
- **Multimaestro**: los datos se pueden cambiar en **cualquiera** de los DC al mismo tiempo.
- **Objetivos de la replicación**: exactitud, integridad, coherencia y buen rendimiento.

### Características técnicas de la replicación

| Característica | Detalles |
| --- | --- |
| Replicación por atributo | Desde Windows Server 2003 la unidad de replicación es el **atributo**, así que los cambios en distintos atributos del mismo objeto se combinan sin conflicto. |
| Replicación por partición | La base de datos de AD se replica **partición por partición**, no entera. (esquema, configuración, dominio, GC, aplicación) |
| Topología automática | **AD diseña automáticamente** cómo deben replicarse los DC entre sí de la forma más eficiente. |

---

## 4. Sitios y servicios

Por defecto, AD da por hecho que todos los DC están en una **red rápida** al replicar. Pero si la red de la empresa está dividida por **enlaces WAN lentos**, la replicación y la autenticación pueden tener problemas.

Un **sitio (Site)** agrupa la estructura de la red para que AD controle la replicación en redes lentas y dirija a los usuarios hacia **el DC de la red más cercana (más rápida)**.

![Sites and replication](../images/ad-sites-replication.svg)

### Qué hacen los sitios y cómo se forman

| Elemento | Detalles |
| --- | --- |
| Control de la replicación | Controla cuándo y con qué frecuencia se replica entre sitios (por la WAN) para reducir la carga del enlace. |
| Localización de servicios | Hace que los clientes se autentiquen con **un DC de su propio sitio**. |
| Definición de sitios | Los sitios se dividen según las redes bien conectadas (subredes). |
| Vínculo de sitios | La ruta de replicación que une un sitio con otro. |
| Costo | Cada vínculo de sitios tiene un costo; se prefieren las rutas de menor costo. |

---

## 5. RODC

### ¿Qué es un RODC?

- Un DC normal puede **leer y escribir** datos, pero un **RODC (Read-Only Domain Controller)** funciona, como dice su nombre, en **solo lectura**.
- Se instala en **ubicaciones físicamente poco seguras** (como sucursales) para que la información importante no quede expuesta aunque roben el DC.

![RODC at a branch office](../images/ad-rodc.svg)

### Funciones de seguridad del RODC

- **No guarda contraseñas**: por defecto, un RODC no guarda información de seguridad sensible (contraseñas).
- **Almacenamiento en caché selectivo**: para mejorar el rendimiento, se puede elegir guardar en caché solo las contraseñas de **usuarios concretos**. Así se acelera la autenticación de los usuarios de esa ubicación sin perder seguridad.

---

## Resumen

- Al unirse a un dominio se crea una **cuenta de equipo** cuya contraseña cambia **cada 30 días** y sirve para establecer un **canal seguro** con el DC.
- No se pueden vincular GPO a los **contenedores Users/Computers**, así que usa `redirusr` y `redircmp` para cambiar la ubicación predeterminada a una OU. Para trabajos masivos usa **LDIFDE**.
- La replicación de AD es **multimaestro**, **por atributo** (desde 2003) y **por partición**, y la topología se crea **automáticamente**.
- Los **sitios** controlan la replicación por enlaces WAN lentos y hacen que los clientes usen un **DC cercano**. Los sitios se conectan con **vínculos de sitios** y **costos**.
- Un **RODC** es un **DC de solo lectura** para ubicaciones poco seguras; por defecto no guarda contraseñas y **solo almacena en caché los usuarios elegidos**.
