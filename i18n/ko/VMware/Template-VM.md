# VMware Template VM

**한국어** | [English](../../../VMware/Template-VM.md) | [日本語](../../ja/VMware/Template-VM.md) | [简体中文](../../zh-CN/VMware/Template-VM.md) | [Español](../../es/VMware/Template-VM.md) | [Français](../../fr/VMware/Template-VM.md)

과제나 대회에서 VM이 여러 대 필요할 때, 모든 OS를 손으로 설치하면 시간이 너무 오래 걸립니다. 대신 VM 한 대를 제대로 설치해 **Template VM**으로 만들고, 나머지는 거기서 **Clone**해서 씁니다.

이 문서는 VMware Workstation에서 Template VM을 준비하는 방법, 거기서 Clone하는 방법, 그리고 Clone한 뒤 각 VM 안에서 고쳐야 할 것을 정리합니다.

---

## 1. Template VM이란?

Template VM은 모든 Clone이 만들어지는 **기준(부모) VM**입니다.

| 용어 | 의미 |
| --- | --- |
| **Template VM** | 한 번 설치해서 기준으로 보관하는 원본 VM |
| **Snapshot** | VM의 특정 시점 상태를 저장한 것. Clone은 Snapshot에서 만들어집니다 |
| **Clone** | Template의 Snapshot을 복사해 만든 새 VM |
| **Template mode** | Template이 실수로 삭제되거나 망가지지 않게 보호하는 VMware 옵션 |

---

## 2. Template VM 준비

### Step 0. 기준 VM 준비

Snapshot을 찍기 전에, 모든 Clone이 공통으로 가져야 할 것을 이 VM에 넣어 둡니다.

- OS와 늘 쓰는 패키지 설치 (`vim`, `ssh`, `curl` … )
- 평소 쓰는 기본 설정 적용 (vimrc, SSH 설정 등)
- 장비마다 달라야 하는 것은 **넣지 않습니다**: 호스트네임, 고정 IP, 인증서, 키
- **VM 전원 끄기** (Power Off / Shut Down Guest)

> Snapshot은 VM이 **꺼진 상태**에서 찍습니다. 꺼진 상태의 Snapshot에서 만든 Clone은 새로 켠 장비처럼 깔끔하게 부팅됩니다.

### Step 1. `Template` 라는 이름의 Snapshot 만들기

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- 이름: `Template`
- 설명(선택): 설치된 것, 날짜 등

### Step 2. Template mode 켜기

**VM → Settings → Options → Advanced → `Enable Template mode (to be used for cloning)` 체크**

![Enable Template mode](../../../VMware/images/enable-template-mode.png)

**왜 하나요?**
- Snapshot이 남고, **실수로 지울 수 없게** 됩니다.
- Template VM 자체도 **삭제로부터 보호**됩니다.
- **Linked clone은 Template의 Snapshot에 의존**하기 때문에 중요합니다. Template이나 그 Snapshot이 사라지면 모든 Linked clone이 깨집니다.

이제 Template VM 준비가 끝났고, Clone을 빠르고 안전하게 할 수 있습니다.

---

## 3. Template에서 Clone하기

**Template VM 우클릭 → Manage → Clone**

1. **Clone from** → `An existing snapshot` → `Template` 선택
2. **Clone type** → 아래 두 가지 중 하나 선택
3. 새 VM 이름(예: `FW`, `DNS`, `WEB1`)과 위치 입력 → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| 동작 방식 | Template의 디스크를 공유하고 바뀐 부분만 저장 | 디스크 전체를 복사 |
| 생성 속도 | **몇 초** | 몇 분 (디스크 크기에 따라) |
| 디스크 사용량 | **매우 적음** | Template과 같음 |
| Template 필요 여부 | **필요** — Template/Snapshot이 없어지면 깨짐 | 불필요 — 완전히 독립 |
| 적합한 용도 | 실습, 대회 연습, 잠깐 쓰는 VM 여러 대 | 오래 쓸 VM, 다른 PC로 옮길 VM |

> 실습과 대회 연습에는 보통 **Linked clone**이 가장 좋습니다. 빠르고 가볍습니다. 대신 그 뒤로 Template VM은 절대 지우거나 수정하지 마세요.

---

## 4. Clone한 뒤: 겹치면 안 되는 값 고치기

Clone은 완전히 똑같은 복사본이라, 일부 값이 Template이나 다른 Clone과 **똑같습니다**. 쓰기 전에 각 Clone에서 고칩니다.

### Linux (Debian / Ubuntu)

| 항목 | 이유 | 명령 |
| --- | --- | --- |
| Hostname | 모든 Clone이 Template 이름을 가짐 | `hostnamectl set-hostname WEB1` 후 `/etc/hosts` 의 `127.0.1.1` 줄 수정 |
| IP 주소 | IP 충돌 방지 | `/etc/network/interfaces` (Debian) 또는 Netplan (Ubuntu) 수정 |
| machine-id | ID가 같으면 DHCP가 서로 다른 Clone에 **같은 IP**를 줄 수 있음 | `rm /etc/machine-id && systemd-machine-id-setup` |
| SSH host key | 모든 Clone이 같은 키를 공유 | `rm /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server` |

```bash
# 예: 새 Clone 안에서 실행한 뒤 재부팅
hostnamectl set-hostname WEB1
sed -i 's/^127.0.1.1.*/127.0.1.1\tWEB1/' /etc/hosts
rm -f /etc/machine-id && systemd-machine-id-setup
rm -f /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server
reboot
```

### Windows Server

Windows Clone은 같은 **SID**를 가지게 되어 Active Directory(도메인 가입 등)에서 문제가 생깁니다.
`Template` Snapshot을 찍기 **전에** Template 안에서 **Sysprep**을 실행합니다.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

- `/generalize` : SID 같은 장비 고유 정보를 지움
- `/oobe` : 다음 부팅 때 처음 설정 화면을 띄움
- `/shutdown` : 끝나면 전원을 끔 → 그다음 Snapshot을 찍음

### 네트워크 어댑터 (MAC 주소)

VMware는 보통 Clone마다 **새 MAC 주소**를 자동으로 줍니다.
그래도 두 Clone의 MAC이 같다면 **VM → Settings → Network Adapter → Advanced → Generate** 를 누릅니다.

---

## 5. 요약

1. 기준 VM 설치 → 전원 끄기
2. Snapshot Manager → `Template` 이라는 Snapshot 만들기
3. Settings → Options → Advanced → **Enable Template mode**
4. Manage → Clone → `Template` Snapshot에서 → **Linked clone**(빠름) 또는 **Full clone**(독립)
5. 각 Clone에서 **hostname, IP, machine-id, SSH host key** 변경 (Windows: Snapshot 전에 **Sysprep**)
