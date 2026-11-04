# VMware Template VM

**한국어** | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | [Français](README.fr.md)

![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)
![Debian](https://img.shields.io/badge/Debian-A81D33?logo=debian&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?logo=ubuntu&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server-2025-0078D4?logo=windows&logoColor=white)

과제나 대회에서 VM이 여러 대 필요할 때, 모든 OS를 손으로 설치하면 시간이 너무 오래 걸립니다. 대신 VM 한 대를 제대로 설치해 **Template VM**으로 만들고, 나머지는 거기서 **Clone**해서 씁니다.

![Template VM에서 Clone까지](../images/template-clone-flow.svg)

## 목차

- [1. 핵심 용어](#1-핵심-용어)
- [2. Template VM 준비](#2-template-vm-준비)
- [3. Template에서 Clone하기](#3-template에서-clone하기)
- [4. Clone한 뒤: 각 Clone을 고유하게 만들기](#4-clone한-뒤-각-clone을-고유하게-만들기)
- [5. 이 폴더의 스크립트](#5-이-폴더의-스크립트)
- [6. 문제 해결](#6-문제-해결)
- [7. 빠른 체크리스트](#7-빠른-체크리스트)
- [참고 자료](#참고-자료)

---

## 1. 핵심 용어

| 용어 | 의미 |
| --- | --- |
| **Template VM** | 한 번 설치해서 기준(부모)으로 보관하는 원본 VM |
| **Snapshot** | VM의 특정 시점 상태를 저장한 것. Clone은 Snapshot에서 만들어집니다 |
| **Linked clone** | 부모의 가상 디스크를 공유하고 자기 변경분만 저장하는 Clone |
| **Full clone** | 부모를 통째로 복사한 독립적인 Clone |
| **Template mode** | 부모를 보호하는 VMware 옵션. 켜져 있는 동안 VM과 그 Snapshot을 **삭제할 수 없습니다** |

---

## 2. Template VM 준비

### Step 0. 기준 VM 준비

모든 Clone이 공통으로 가져야 할 것만 넣고, 장비마다 달라야 하는 것은 넣지 않습니다.

| ✅ Template에 넣을 것 | ❌ Template에 넣지 말 것 |
| --- | --- |
| OS 업데이트, 공통 패키지 (`vim`, `ssh`, `curl` … ) | 특정 역할의 호스트네임 (FW, DNS, WEB1 …) |
| 평소 쓰는 vimrc, SSH 설정, 시간대 | 고정 IP 주소 |
| VMware Tools / open-vm-tools | 특정 서버용 인증서, 개인 키, 비밀번호 |
| | 도메인 가입 (Windows) |

필요하면 [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) (Linux) 또는 [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) (Windows)로 VM을 정리합니다. 두 스크립트 모두 끝나면 VM 전원을 끕니다.

> **Snapshot을 찍기 전에 VM 전원을 끕니다.** VMware는 Clone하려면 부모가 꺼져 있어야 하고, 꺼진 상태의 Snapshot에서 만든 Clone은 새로 켠 장비처럼 깔끔하게 부팅됩니다.

### Step 1. `Template` 이라는 Snapshot 만들기

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- 이름: `Template`
- 설명(선택): 설치된 것, 날짜 등

### Step 2. Template mode 켜기

**VM → Settings → Options → Advanced → `Enable Template mode (to be used for cloning)` 체크**

![Enable Template mode](../images/enable-template-mode.png)

**왜 하나요?**
- Template mode가 켜져 있으면 **VM과 그 Snapshot을 삭제할 수 없습니다**.
- **Linked clone은 부모의 Snapshot에 의존**합니다. 부모나 Snapshot이 사라지면 모든 Linked clone이 깨지는데, Template mode가 이걸 막아 줍니다.
- VMware도 Linked clone을 만들 때는 부모에 Template mode를 켜라고 권장합니다.

> 원격(remote) 가상 머신에는 Template mode를 켤 수 없습니다.

이제 Template VM 준비가 끝났습니다.

---

## 3. Template에서 Clone하기

**Template VM 우클릭 → Manage → Clone**

1. **Clone from** → `An existing snapshot` → `Template` 선택
   (Template mode가 켜져 있으면 *현재 상태(current state)* 에서는 Clone할 수 **없습니다**. 그래서 모든 Clone이 같은 Snapshot을 기준으로 만들어집니다.)
2. **Clone type** → `Create a linked clone` 또는 `Create a full clone`
3. 새 VM 이름(예: `FW`, `DNS`, `WEB1`)과 위치 입력 → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| 동작 방식 | 부모 디스크를 공유하고 바뀐 부분만 저장 | 디스크 전체를 복사 |
| 생성 속도 | **몇 초** | 몇 분 (디스크 크기에 따라) |
| 디스크 사용량 | **매우 적음** | 부모와 같음 |
| 성능 | 조금 낮음 | 대체로 더 좋음 |
| 부모 필요 여부 | **필요** — 부모/Snapshot이 없어지면 깨짐 | 불필요 — 완전히 독립 |
| 부모의 Snapshot | 선택한 Snapshot을 사용 | 부모의 Snapshot에 접근 불가 |
| 적합한 용도 | 실습, 대회 연습, 잠깐 쓰는 VM 여러 대 | 오래 쓸 VM, 다른 PC로 옮길 VM |

> 실습과 대회 연습에는 보통 **Linked clone**이 가장 좋습니다. 대신 그 뒤로 Template VM은 절대 지우거나 바꾸지 마세요.

---

## 4. Clone한 뒤: 각 Clone을 고유하게 만들기

Clone 마법사는 Clone마다 **새 MAC 주소와 UUID**를 주지만, 나머지 — VM 이름, 호스트네임, **고정 IP** — 는 부모와 똑같습니다. 공용 네트워크에 연결하기 **전에** 각 Clone에서 고칩니다.

### Linux (Debian / Ubuntu)

| 항목 | 이유 | 해결 |
| --- | --- | --- |
| Hostname | 모든 Clone이 Template 이름을 가짐 | `hostnamectl set-hostname WEB1` + `/etc/hosts` 의 `127.0.1.1` 줄 |
| IP 주소 | IP 충돌 방지 | `/etc/network/interfaces` (Debian) 또는 `/etc/netplan/` (Ubuntu) |
| machine-id | ID가 같으면 DHCP가 서로 다른 Clone에 **같은 IP**를 줄 수 있음 | `rm /etc/machine-id && systemd-machine-id-setup` |
| SSH host key | 모든 Clone이 같은 키를 공유 | `rm /etc/ssh/ssh_host_* && ssh-keygen -A` |

IP를 뺀 위 작업을 명령 하나로:

```bash
sudo ./post-clone-linux.sh WEB1
```

→ [`scripts/post-clone-linux.sh`](../scripts/post-clone-linux.sh)

### Windows Server

Windows Clone은 같은 **SID**를 가지게 되어 Active Directory(도메인 가입 등)가 깨집니다.
`Template` Snapshot을 찍기 **전에** Template 안에서 **Sysprep**을 실행합니다.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

| 옵션 | 의미 |
| --- | --- |
| `/generalize` | SID 같은 장비 고유 정보를 지움 |
| `/oobe` | 다음 부팅 때 처음 설정 화면을 띄움 |
| `/shutdown` | 끝나면 전원을 끔 → 그다음 Snapshot을 찍음 |

→ [`scripts/Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) 은 관리자 권한과 도메인 가입 여부를 확인한 뒤 위 명령을 실행합니다.

---

## 5. 이 폴더의 스크립트

| 스크립트 | 실행 위치 | 언제 | 하는 일 |
| --- | --- | --- | --- |
| [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) | Linux Template | Snapshot 전 | apt 캐시·로그·기록·DHCP 임대 정리, machine-id 비우기, 전원 끄기 |
| [`post-clone-linux.sh`](../scripts/post-clone-linux.sh) | 각 Linux Clone | 첫 부팅 | 새 hostname, machine-id, SSH host key |
| [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) | Windows Template | Snapshot 전 | Sysprep `/generalize /oobe /shutdown` |

---

## 6. 문제 해결

| 증상 | 원인 | 해결 |
| --- | --- | --- |
| 두 Clone이 DHCP에서 같은 IP를 받음 | machine-id가 같음 | machine-id 다시 생성 ([4번](#4-clone한-뒤-각-clone을-고유하게-만들기)) |
| `WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED` / 모든 서버 지문이 같음 | SSH host key 공유 | host key 다시 생성 후 클라이언트에서 `ssh-keygen -R <host>` |
| `sudo: unable to resolve host` | `/etc/hosts` 에 예전 이름이 남아 있음 | `127.0.1.1` 줄 수정 |
| Windows Clone이 도메인에 가입되지 않음 | SID가 같음 | Snapshot 전에 Template에서 Sysprep |
| Linked clone이 부모 디스크를 못 찾아 시작 안 됨 | Template VM을 옮기거나 지움 | Template은 제자리에 두고 Template mode 사용 |
| "현재 상태에서 Clone"이 비활성화됨 | Template mode가 켜져 있음 | `Template` Snapshot에서 Clone |
| 두 Clone의 MAC 주소가 같음 | 마법사 대신 VMX를 손으로 복사함 | VM → Settings → Network Adapter → Advanced → **Generate** |

---

## 7. 빠른 체크리스트

- [ ] 기준 VM 설치·업데이트, 역할별 설정은 넣지 않음
- [ ] (Linux) `prepare-template-linux.sh` / (Windows) Sysprep
- [ ] VM 전원 끔
- [ ] `Template` Snapshot 생성
- [ ] **Enable Template mode** 체크
- [ ] `Template` Snapshot에서 Clone → Linked 또는 Full
- [ ] 각 Clone에서 hostname, IP, machine-id, SSH host key 변경

---

## 참고 자료

- Broadcom / VMware Docs — [Enable Template Mode for a Parent Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-B544E04E-8EDD-4918-A486-7F3B7100E3C0.html)
- Broadcom / VMware Docs — [Clone a Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-C86E70AD-85A9-4D85-AEBB-135AA5648554.html)
- Broadcom / VMware Docs — [Using Full Clones](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-F8AC53B9-0577-4410-9935-B6E332E87554.html)
- Microsoft Learn — [Sysprep (Generalize) a Windows installation](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep--generalize--a-windows-installation)
- Microsoft Learn — [Sysprep command-line options](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep-command-line-options)
- systemd — [machine-id(5)](https://www.mankier.com/5/machine-id), [systemd-machine-id-setup(1)](https://www.mankier.com/1/systemd-machine-id-setup)
