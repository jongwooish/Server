# Linux GUI Install

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

Cómo crear una **VM con GUI** clonando la VM plantilla Linux CLI.

> Para la VM CLI, lee primero [01. Guía de instalación de una VM Debian](../../01-Debian-VM-Install/README/README.es.md).

## Contenido

- [1. Full clone de la VM Debian CLI](#1-full-clone-de-la-vm-debian-cli)
- [2. Arrancar Linux](#2-arrancar-linux)
- [3. Instalar la GUI](#3-instalar-la-gui)
- [4. Comprobación](#4-comprobación)
- [Resumen](#resumen)

---

## 1. Full clone de la VM Debian CLI

En la VM Debian CLI, selecciona **Manage → Clone**.

![Clone menu](../images/01-clone-menu.png)

Pulsa Enter → Enter y luego selecciona **Create a full clone**.

![Create a full clone](../images/02-full-clone.png)

Poner **GUI** en el nombre para distinguirla de la VM CLI te facilitará la vida más adelante.

![Virtual machine name](../images/03-vm-name.png)

Cuando se haya creado el clon, pulsa Enter (Close).

![Clone done](../images/04-clone-done.png)

---

## 2. Arrancar Linux

Enciende la VM.

![Power on](../images/05-power-on.png)

Inicia sesión como usuario **root**.

![root login](../images/06-root-login.png)

---

## 3. Instalar la GUI

Escribe el siguiente comando y pulsa Enter.

```bash
tasksel
```

![tasksel](../images/07-tasksel.png)

Marca **Debian desktop environment** con la barra espaciadora y pulsa Enter.

![Debian desktop environment](../images/08-desktop-environment.png)

Espera a que terminen la descarga y la instalación.

![Installing packages](../images/09-installing.png)

Reinicia con `reboot` y el sistema arrancará en la GUI.

```bash
reboot
```

![reboot](../images/10-reboot.png)

---

## 4. Comprobación

Si ves una pantalla como esta, ya puedes usarla como la **VM original de Linux con GUI**.

![GUI check](../images/11-gui-check.png)

---

## Resumen

- La VM con GUI se crea con un **full clone de la VM Debian CLI**. Pon GUI en el nombre para distinguirlas.
- Inicia sesión como root y selecciona **Debian desktop environment** en **`tasksel`**.
- Cuando termine la instalación, haz **`reboot`**; cuando aparezca la GUI, úsala como VM original con GUI.
