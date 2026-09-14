import subprocess
import re

# Global constants
DEBUG_ON = False
DEBUG_IP_ON = False
ERROR_ON = False
CONFIG_FILE = "/home/pi/sc/sc28config.txt"
# For Hostname
HOSTNAME_PREFIX = "sc28-"
TEMPFILE = "/home/pi/sc/temp.txt"
HOSTSFILE = "/etc/hosts"
HOSTNAMEFILE = "/etc/hostname"
# For IP Address
ASK = '0'
AUTO = '1'
STATIC = '2'
DHCP_FILE = '/etc/dhcpcd.conf'
RESOLV_FILE = '/etc/resolv.conf'
TEMP_FILE = '/tmp/dhcpcd.conf'


def read_config():
    """Read SC28 Config file"""
    config = {}
    config_lines = open(CONFIG_FILE, 'r').readlines()
    for line in config_lines:
        if '=' in line:
            (key, value) = line.rstrip().split('=')
            config[key] = value.strip('"')
            if DEBUG_ON:
                print("read_config:", key, config[key])
    # If serial number not found in config file, write a dummy config file
    try:
        serial = config["SerialNumber"]
    except:
        config["SerialNumber"] = "00000"
        write_config(config)
    return config

def write_config(config):
    """Write SC28 Config file"""
    if DEBUG_ON:
        print("write_config: SerialNumber", config["SerialNumber"])
    try:
        with open(CONFIG_FILE, 'w') as fp:
            fp.write('[SC28Config]\n')
            fp.write('SerialNumber="' + config["SerialNumber"] + '"\n')
    except:
        print("Error: Unable to write config file")
        return False
    return True
        
def get_serial():
    """Get SC28 serial number"""
    config = read_config()
    if DEBUG_ON:
        print("get_serial:", config["SerialNumber"])
    return config["SerialNumber"]

def set_serial(serial):
    """Set SC28 serial number"""
    if DEBUG_ON:
        print("set_serial:", serial)
    config = read_config()
    config["SerialNumber"] = serial
    return write_config(config)

def set_hostname(serial):
    """Set hostname of SC28 device"""
    # Build new hostname
    newhostname = HOSTNAME_PREFIX + serial
    # Read old hostname file
    with open(HOSTNAMEFILE, "r") as file:
        oldhostname = file.read().rstrip()
    if DEBUG_ON:
        print("old hostname:", oldhostname)
        print("new hostname:", newhostname)
    # Create temporary hostname file
    with open(TEMPFILE, "w") as file:
        file.write(newhostname)
    # Move temporary hostname file to hostname file
    command = "sudo mv " + TEMPFILE + " " + HOSTNAMEFILE
    if DEBUG_ON:
        print("set hostname:", command)
    try:
        subprocess.run(command, shell=True)
    except:
        print("Unable to update", HOSTNAMEFILE)
        return False
    # Read old hosts file
    with open(HOSTSFILE, "r") as file:
        whole_file = file.read()
    # Update hostname in file
    whole_file = whole_file.replace(oldhostname, newhostname)
    if DEBUG_ON:
        print(whole_file)
    # Create temporary hosts file
    with open(TEMPFILE, "w") as file:
        file.write(whole_file)
    # Move temporary file to hosts file
    command = "sudo mv " + TEMPFILE + " " + HOSTSFILE
    if DEBUG_ON:
        print("set hostname:", command)
    try:
        subprocess.run(command, shell=True)
    except:
        print("Unable to update", HOSTSFILE)
        return False    
    return True

def get_ip():
    """Get DCHP/Static mode, IPv4 address, mask bits, Gateway and DNS"""
    
    # Set all to uninitialised state
    ip_mode = ASK
    ip_addr = '0'
    ip_mask_bits = 0
    ip_gateway = '0'
    ip_dns = '0'
    
    # Read DHCP conf file
    try:
        with open(DHCP_FILE, 'r') as f:
            ip_lines = f.readlines()
    except:
        if ERROR_ON:
            print('Unable to read', DHCP_FILE)
        return(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns)

    # Parse for SC28 Static IP Settings, if any
    found = False
    for line in ip_lines:
        if '# SC28 Static IP Settings' in line:
            found = True
        if found:
            if 'static ip_address' in line:
                ip_mode = STATIC
                (a, ip_addr, ip_mask_bits, z) = re.split('[=/\n]', line, maxsplit=4)
            if 'static routers' in line:
                (a, ip_gateway, z) = re.split('[=\n]', line, maxsplit=3)
            if 'static domain_name_servers' in line:
                (a, ip_dns, z) = re.split('[=\n]', line, maxsplit=3)
                
    # If SC28 settings were in the file, return them
    if found:
        if DEBUG_IP_ON:
            print('Read static IP settings from', DHCP_FILE)
            print(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns)
        return(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns)

    # No SC28 settings found - defaults to AUTO
    ip_mode = AUTO

    # Call "ip r" command
    try:
        ip_out = subprocess.run(['/sbin/ip', 'r'],
            check=True, capture_output=True, text=True).stdout
    except:
        if ERROR_ON:
            print('Unable to get details from ip r command')
    else:
        # Parse output for IP maskbits and Gateway
        ip_vals = ip_out.split()
        found = ''
        for val in ip_vals:
            # After via, get gateway
            if found == 'via':
                ip_gateway = val
                found = ''
            # After src, get IP address
            if found == 'src':
                ip_addr = val
                found = ''
            # Check for via and src
            if val == 'via':
                found = 'via'
            if val == 'src':
                found = 'src'
            # Maskbits are after /
            if '/' in val:
                ip_mask_bits = val.split('/')[1]
        
    # Read Resolv conf file
    try:
        with open(RESOLV_FILE, 'r') as f:
            ip_lines = f.readlines()
    except:
        if ERROR_ON:
            print('Unable to get details from', RESOLV_FILE)
    else:
        # Parse for DNS server
        for line in ip_lines:
            if ('nameserver' in line) and ('.' in line):
                (a, ip_dns, z) = re.split('[ \n]', line, maxsplit=3)
                break

    if DEBUG_IP_ON:
        print('Current values obtained via DHCP')
        print(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns)
    return(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns)
    
def set_ip(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns):
    """Set DCHP/Static mode, IPv4 address, mask bits, Gateway and DNS. Reboot"""
    if DEBUG_IP_ON:
        print('Setting values')
        print(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns)

    # Read DHCP conf file
    try:
        with open(DHCP_FILE, 'r') as f:
            ip_lines = f.readlines()
    except:
        if ERROR_ON:
            print('Unable to read', DHCP_FILE)
        return(False)
    # Find exisiting SC28 Static IP Settings, if any
    try:
        line_num = ip_lines.index('# SC28 Static IP Settings\n')
    except ValueError:
        # Not found - nothing to do
        pass
    else:
        # Delete to end of file
        del ip_lines[line_num:]

    # If Static IP address, append settings to file
    if ip_mode == STATIC:
        ip_lines.append('# SC28 Static IP Settings\n')
        ip_lines.append('interface eth0\n')
        ip_lines.append('static ip_address=' + ip_addr + '/' + ip_mask_bits + '\n')
        ip_lines.append('static routers=' + ip_gateway + '\n')
        ip_lines.append('static domain_name_servers=' + ip_dns + '\n')

    # Write file to temporary location
    try:
        with open(TEMP_FILE, 'w') as f:
            f.writelines(ip_lines)
    except:
        if ERROR_ON:
            print('Unable to write', TEMP_FILE)
        return(False)
    else:
        # Move temp file to destination
        try:
            subprocess.Popen(['sudo', '/bin/mv', TEMP_FILE, DHCP_FILE])
        except:
            if ERROR_ON:
                print(f'Unable to copy {TEMP_FILE} to {DHCP_FILE}')
            return(False)
        else:
            # Reboot OS
            try:
                subprocess.Popen(['sudo', 'reboot'])
            except:
                if ERROR_ON:
                    print('Unable to reboot OS')

