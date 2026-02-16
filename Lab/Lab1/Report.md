# Практическая работа №1

## 1. Создание виртуальной машины

![параметры виртуальной машины в процессе создания (имя ВМ, память, диск)](screenshots/01-vm-settings.png)

![командная строка Ubuntu после установки с приглашением фамилия@фамилия:~$](screenshots/02-vm-console.png)

## 2. Информация о системе

![вывод cat ~/report/01-system.txt](screenshots/03-system-info.png)

## 3. Сеть: IP-адрес и открытые порты

![вывод ip addr show](screenshots/04-ip-addr.png)

![вывод sudo ss -tlnp](screenshots/05-ports.png)

## 4. Сервис SSH

![вывод sudo systemctl status ssh](screenshots/06-ssh-status.png)

![вывод sudo ss -tlnp | grep ssh](screenshots/07-ssh-port.png)

## 5. Пользователи и групп

![вывод grep '/bin/bash' /etc/passwd](screenshots/08-users.png)

![процесс создания пользователя boardy](screenshots/09-new-user.png)

![вывод id boardy](screenshots/10-user-check.png)

## 6. Дерево каталогов

![вывод ls -la /](screenshots/11-root-tree.png)

![вывод ls -la ~](screenshots/12-home-tree.png)

## 7. Права доступа

![вывод ls -ld / /etc /var /tmp /home](screenshots/13-permissions.png)

![три состояния testfile.txt (до, после chmod 755, после chmod 600)](screenshots/14-chmod.png)

## 8. Установленные пакеты и сервисы

1. 
![вывод dpkg -l | grep -E 'openssh|python|git|curl|vim|nano'](screenshots/15-packages.png)
2. 
![вывод dpkg -l | grep -E 'openssh|python|git|curl|vim|nano'](screenshots/15-packages-2.png)

![вывод systemctl list-units --type=service --state=running](screenshots/16-services.png)

## 9. Конвейер и перенаправление

![вывод ps aux --sort=-%mem | head -11](screenshots/17-top-processes.png)

![вывод подсчёта процессов по пользователям](screenshots/18-process-count.png)

![вывод топ-10 больших файлов в /var](screenshots/19-big-files.png)

## 10. Итоговый файл

![вывод ls -lh ~/report/ со всеми файлами](screenshots/20-report-files.png)
