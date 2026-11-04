# VMware Template VM

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | **Español** | [Français](README.fr.md)

![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)
![Debian](https://img.shields.io/badge/Debian-A81D33?logo=debian&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?logo=ubuntu&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server-2025-0078D4?logo=windows&logoColor=white)

Cuando una práctica o una prueba de competición necesita muchas VM, instalar cada sistema operativo a mano lleva demasiado tiempo. En su lugar, instala bien una VM, conviértela en una **Template VM** y **clona** el resto a partir de ella.

![De la Template VM a los clones](../images/template-clone-flow.svg)

## Contenido

- [1. Términos clave](#1-términos-clave)
- [2. Preparar la Template VM](#2-preparar-la-template-vm)
- [3. Clonar a partir de la plantilla](#3-clonar-a-partir-de-la-plantilla)
- [4. Después de clonar: hacer único cada clon](#4-después-de-clonar-hacer-único-cada-clon)
- [5. Scripts de esta carpeta](#5-scripts-de-esta-carpeta)
- [6. Solución de problemas](#6-solución-de-problemas)
- [7. Lista de comprobación rápida](#7-lista-de-comprobación-rápida)
- [Referencias](#referencias)

---

## 1. Términos clave

| Término | Significado |
| --- | --- |
| **Template VM** | La VM original que instalas una vez y guardas como base (padre) |
| **Snapshot** | Estado guardado de la VM en un momento concreto. Los clones se crean a partir de un snapshot |
| **Linked clone** | Clon que comparte el disco virtual del padre y solo guarda sus propios cambios |
| **Full clone** | Copia completa e independiente del padre |
| **Template mode** | Opción de VMware que protege al padre: mientras está activa, la VM y sus snapshots **no se pueden borrar** |

---

## 2. Preparar la Template VM

### Paso 0. Dejar lista la VM base

Mete en esta VM todo lo que deben compartir los clones, y nada que tenga que ser único.

| ✅ Poner en la plantilla | ❌ Dejar fuera de la plantilla |
| --- | --- |
| Actualizaciones del sistema, paquetes comunes (`vim`, `ssh`, `curl` … ) | Nombre de host de un rol concreto (FW, DNS, WEB1 …) |
| Tu vimrc habitual, ajustes de SSH, zona horaria | Direcciones IP fijas |
| VMware Tools / open-vm-tools | Certificados, claves privadas o contraseñas de un servidor concreto |
| | Unión al dominio (Windows) |

Si quieres, ejecuta [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) (Linux) o [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) (Windows) para limpiar la VM. Ambos apagan la VM al terminar.

> **Apaga la VM antes de tomar el snapshot.** VMware exige que el padre esté apagado para clonarlo, y los clones creados a partir de un snapshot apagado arrancan limpios, como una máquina recién encendida.

### Paso 1. Crear un snapshot llamado `Template`

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- Nombre: `Template`
- Descripción (opcional): qué está instalado, la fecha, etc.

### Paso 2. Activar el Template mode

**VM → Settings → Options → Advanced → marca `Enable Template mode (to be used for cloning)`**

![Enable Template mode](../images/enable-template-mode.png)

**¿Por qué?**
- Con el template mode activado, **la VM y sus snapshots no se pueden borrar**.
- **Los linked clones dependen del snapshot del padre.** Si el padre o su snapshot desaparecen, todos los linked clones se rompen; el template mode lo evita.
- VMware recomienda activar el template mode en el padre siempre que crees linked clones.

> El template mode no se puede activar en una máquina virtual remota.

La Template VM ya está lista.

---

## 3. Clonar a partir de la plantilla

**Clic derecho en la Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → selecciona `Template`
   (Con el template mode activado **no** se puede clonar desde el *estado actual*; así todos los clones parten del mismo snapshot.)
2. **Clone type** → `Create a linked clone` o `Create a full clone`
3. Escribe el nombre de la nueva VM (p. ej. `FW`, `DNS`, `WEB1`) y su ubicación → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| Cómo funciona | Comparte el disco del padre y guarda solo los cambios | Copia el disco completo |
| Velocidad de creación | **Segundos** | Varios minutos (según el tamaño del disco) |
| Uso de disco | **Muy pequeño** | Igual que el padre |
| Rendimiento | Algo menor | En general, mejor |
| ¿Necesita al padre? | **Sí** — se rompe si el padre o el snapshot desaparecen | No — totalmente independiente |
| Snapshots del padre | Usa el snapshot elegido | No tiene acceso a los snapshots del padre |
| Ideal para | Prácticas, entrenamiento de competición, muchas VM de uso corto | VM que vas a conservar o llevar a otro PC |

> Para prácticas y entrenamiento de competición, los **linked clones** suelen ser la mejor opción. Eso sí, no borres ni cambies nunca la Template VM después.

---

## 4. Después de clonar: hacer único cada clon

El asistente de clonación da a cada clon una **dirección MAC y un UUID nuevos**, pero todo lo demás — nombre de la VM, nombre de host, **IP fija** — es idéntico al del padre. Corrígelo en cada clon **antes** de conectarlo a una red compartida.

### Linux (Debian / Ubuntu)

| Elemento | Por qué | Solución |
| --- | --- | --- |
| Hostname | Todos los clones tienen el nombre de la plantilla | `hostnamectl set-hostname WEB1` + la línea `127.0.1.1` de `/etc/hosts` |
| Dirección IP | Evitar conflictos de IP | `/etc/network/interfaces` (Debian) o `/etc/netplan/` (Ubuntu) |
| machine-id | Con el mismo ID, el DHCP puede dar **la misma IP** a clones distintos | `rm /etc/machine-id && systemd-machine-id-setup` |
| Claves de host SSH | Todos los clones comparten las mismas claves | `rm /etc/ssh/ssh_host_* && ssh-keygen -A` |

Todo lo anterior (salvo la IP) con un solo comando:

```bash
sudo ./post-clone-linux.sh WEB1
```

→ [`scripts/post-clone-linux.sh`](../scripts/post-clone-linux.sh)

### Windows Server

Los clones de Windows comparten el mismo **SID**, lo que rompe Active Directory (unión al dominio, etc.).
Ejecuta **Sysprep** dentro de la plantilla **antes** de tomar el snapshot `Template`.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

| Opción | Significado |
| --- | --- |
| `/generalize` | Elimina la información propia de la máquina, como el SID |
| `/oobe` | Muestra la pantalla de configuración inicial en el siguiente arranque |
| `/shutdown` | Apaga al terminar → después toma el snapshot |

→ [`scripts/Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) comprueba los permisos de administrador y si el equipo está en un dominio, y luego ejecuta el comando anterior.

---

## 5. Scripts de esta carpeta

| Script | Dónde | Cuándo | Qué hace |
| --- | --- | --- | --- |
| [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) | Plantilla Linux | Antes del snapshot | Limpia la caché de apt, los registros, el historial y las concesiones DHCP; vacía machine-id; apaga |
| [`post-clone-linux.sh`](../scripts/post-clone-linux.sh) | Cada clon Linux | Primer arranque | Nuevo hostname, machine-id y claves de host SSH |
| [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) | Plantilla Windows | Antes del snapshot | Sysprep `/generalize /oobe /shutdown` |

---

## 6. Solución de problemas

| Síntoma | Causa | Solución |
| --- | --- | --- |
| Dos clones reciben la misma IP por DHCP | Mismo machine-id | Regenerar machine-id ([sección 4](#4-después-de-clonar-hacer-único-cada-clon)) |
| `WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED` / misma huella en todos los servidores | Claves de host SSH compartidas | Regenerar las claves de host y ejecutar `ssh-keygen -R <host>` en el cliente |
| `sudo: unable to resolve host` | `/etc/hosts` aún tiene el nombre antiguo | Corregir la línea `127.0.1.1` |
| El clon de Windows no puede unirse al dominio | Mismo SID | Ejecutar Sysprep en la plantilla antes del snapshot |
| El linked clone no arranca: falta el disco del padre | Se movió o borró la Template VM | Dejar la plantilla en su sitio y usar template mode |
| "Clonar desde el estado actual" aparece en gris | El template mode está activado | Clonar desde el snapshot `Template` |
| Dos clones tienen la misma dirección MAC | Se copió el VMX a mano en lugar de usar el asistente | VM → Settings → Network Adapter → Advanced → **Generate** |

---

## 7. Lista de comprobación rápida

- [ ] VM base instalada y actualizada, sin nada propio de un rol
- [ ] (Linux) `prepare-template-linux.sh` / (Windows) Sysprep
- [ ] VM apagada
- [ ] Snapshot `Template` creado
- [ ] **Enable Template mode** marcado
- [ ] Clonar desde el snapshot `Template` → linked o full
- [ ] En cada clon: hostname, IP, machine-id y claves de host SSH

---

## Referencias

- Broadcom / VMware Docs — [Enable Template Mode for a Parent Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-B544E04E-8EDD-4918-A486-7F3B7100E3C0.html)
- Broadcom / VMware Docs — [Clone a Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-C86E70AD-85A9-4D85-AEBB-135AA5648554.html)
- Broadcom / VMware Docs — [Using Full Clones](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-F8AC53B9-0577-4410-9935-B6E332E87554.html)
- Microsoft Learn — [Sysprep (Generalize) a Windows installation](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep--generalize--a-windows-installation)
- Microsoft Learn — [Sysprep command-line options](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep-command-line-options)
- systemd — [machine-id(5)](https://www.mankier.com/5/machine-id), [systemd-machine-id-setup(1)](https://www.mankier.com/1/systemd-machine-id-setup)
