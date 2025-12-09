# SC28 Main program
TH_METER = False

import time
import tcp
import daq
import ipc
import config
import error_codes

# Global constants
DEBUG_ON = True
ERROR_ON = True
COMMS_TIMEOUT = 20 # seconds of no communications before TCP hang up and retry
# Global variables
tcp_connected = False
time_now = time.time()
mydaq = None

#Firmware Version
FIRMWARE_VERSION = "2.21"



def comms(mytcp, mydaq):
    """ Communicate between SC11/SI and TCP """
    # 1. Send Ready when connected
    # 2. Handle V, R, S, G, Z commands
    # 3. Communicate with SC11/SI via daq functions
    # 4. If error, quit immediately and return with tcp and/or daq false
    # 5. If no error, return with both true
    global environment_state
    global time_now
    tcp_connected = True
    daq_connected = True
    # Accept commands from PC
    start_time = time.time()
    # Loop receiving data from Ethernet
    while True:
        # Get data from Ethernet into local_buffer
        try:
            (numbytes, local_buffer) = mytcp.receive_string()
        except tcp.TcpError:
            mytcp.close()
            tcp_connected = False
            return (tcp_connected, daq_connected)
        # Check time waiting for a command
        wait_time = time.time() - start_time
        # If data received, break out of loop    
        if numbytes > 0:
            break
        if wait_time > COMMS_TIMEOUT:
            # Timed out. Hang up and listen for re-connect.
            if ERROR_ON:
                print(f"Timed out waiting {wait_time*1000:.1f} ms for commands")
            mytcp.send_string("Z 4")
            mytcp.close()
            tcp_connected = False
            return (tcp_connected, daq_connected)
        # Poll every 1 ms
        time.sleep(0.001)

    # V command - Version
    if local_buffer[0] == "V":
        try:
            # Get version and sensor info from SC11/SI
            (sc11_version, scmag1, scmag2) = mydaq.get_version()
        except daq.DaqError:
            response = "V 5 0 0 0 0 0" # Failed
            mydaq.close()
            daq_connected = False
        else:
            # Get temperature and humidity sensor status
            (ok, temperature, humidity) =(True, False, False)
            if ok:
                temprh = "1"
            else:
                temprh = "0"
            response = f"V 0 2.21 2.21 3 0 1"
  
    # R command - Reset DC sensor
    elif local_buffer[0] == "R":
        response = "R 0" # Ok
        # try:
        #     # mydaq.dc_reset()
        # except daq.DaqError as err:
        #     if ERROR_ON:
        #         print("Unable to reset DC sensor: ", err)
        #     response = "R 5" # Failed
        #     mydaq.close()
        #     daq_connected = False

    # M command - Turn microphone check signal on/off
    elif local_buffer[0] == "M":
        mic_check_state = local_buffer[2:]
        response = "M 0" # Ok
        try:
            mydaq.mic_check(mic_check_state)
        except daq.DaqError as err:
            if ERROR_ON:
                print("Unable to set microphone check: ", err)
            response = "M 5" # Failed
            mydaq.close()
            daq_connected = False
    
    # F command - Set patlite according to enviroment state
    elif local_buffer[0] == "F":
        environment_state = int(local_buffer[2:])
        response = "F 0" # Ok
        # if not mypatlite.set(environment_state):
        #     if ERROR_ON:
        #         print("Unable to set patlite")
        #     response = "F 5" # Failed

    # S command - Start DAQ
    elif local_buffer[0] == "S":
        response = "S 0" # Ok
        try:
            mydaq.start()
        except daq.DaqError as err:
            if ERROR_ON:
                print("Unable to start DAQ: ", err)
            response = "S 5" # Failed
            mydaq.close()
            daq_connected = False

    # G command - Get data
    elif local_buffer[0] == "G":
        # Send no response. Just get data from daq and send it to tcp.
        response = ""
        # Refresh Patlite in case it has just been plugged in
        # mypatlite.refresh()
        # Get DAQ data
        try:
            mydaq.read()
        except daq.DaqError as err:
            if ERROR_ON:
                print("Unable to read DAQ: ", err)
            mydaq.close()
            daq_connected = False
            return (tcp_connected, daq_connected)
        # Send arrays of I16 to TCP. Data is transmitted as char
        # and re-constructed into compressed I16 in PC
        # Send DAQ data to PC
        data_start_time = time.time()
        start = 0
        for i in range(0,10):
            end = start + mydaq.daq_size_array[i]
            try:
                mytcp.send_binary(mydaq.daq_buffer[start:end])
            except tcp.TcpError as err:
                # TCP error - lost connection to PC
                if ERROR_ON:
                    print("Error sending DaqBuffer: ", err)
                mytcp.close()
                tcp_connected = False
                # Return failed state
                return (tcp_connected, daq_connected)
            start = end
        # Report elapsed time and duration of data transmission
        time_prev = time_now
        time_now = time.time()
        elapsed_time_ms = (time_now - time_prev) * 1000
        data_duration_ms = (time_now - data_start_time) * 1000
        if DEBUG_ON:
            print(f"TCP data elapsed time / duration: {elapsed_time_ms:.1f} {data_duration_ms:.1f} ms")

    # D command - Report dataloss
    elif local_buffer[0] == "D":
        # Get number of bytes lost since last call. No longer used.
        # Data loss is now reported in the TempRH block of 4 values.
        response = "D 0"

    # Z command - Stop DAQ
    elif local_buffer[0] == "Z":
        response = "Z 0" # Ok
        try:
            mydaq.stop()
        except daq.DaqError as err:
            if ERROR_ON:
                print("Unable to stop DAQ: ", err)
            response = "Z 5" # Failed
            mydaq.close()
            daq_connected = False

    # X command - Close connection - do not reply
    elif local_buffer[0] == "X":
        mytcp.close()
        tcp_connected = False
        return (tcp_connected, daq_connected)
    
    # L command - Load firmware
    elif local_buffer[0] == "L":
        manifest_bytes = int(local_buffer[2:])
        if manifest_bytes > 0:
            # program.loader(manifest_bytes, mydaq, mytcp)
            response = ""
        else:
            if ERROR_ON:
                print("Cannot get manifest size")
            response = "L 2 0"
            
    # I command - Get or set IP address
    elif local_buffer[0] == "I":
        # Get command parameters
        ip_parms = local_buffer[2:].split()
        # If mode is ASK, get existing settings
        if ip_parms[0] == config.ASK:
            (ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns) = config.get_ip()
            if (ip_mode == config.ASK) or (ip_dns == '0'):
                # Failed to get
                response = "I 5 0 0 0 0 0"
            else:
                response = f"I 0 {ip_mode} {ip_addr} {ip_mask_bits} {ip_gateway} {ip_dns}"
        # If mode is AUTO, set AUTO. The other parms are ignored
        elif ip_parms[0] == config.AUTO:
            if not config.set_ip(config.AUTO, '0', 0, '0','0'):
                # Failed to set
                response = "I 5 0 0 0 0 0"
            # Else OS will reboot
        # If mode is STATIC, set all parms
        elif ip_parms[0] == config.STATIC:
            (ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns) = ip_parms
            if not config.set_ip(ip_mode, ip_addr, ip_mask_bits, ip_gateway, ip_dns):
                # Failed to set
                response = "I 5 0 0 0 0 0"
            # Else OS will reboot
        # Else unknown mode - bad parameter error
        else:
            response = "I 2 0 0 0 0 0"
    # Unknown command
    else:
        if ERROR_ON:
            print("Unknown command:", local_buffer)
        response = "E 1"
    
    # Send response to PC
    if response != "":
        try:
            mytcp.send_string(response)
        except tcp.TcpError:
            if ERROR_ON:
                print("Error sending response:", response)
            mytcp.close()
            tcp_connected = False
            return (tcp_connected, daq_connected)

    # Normal return from comms
    return (tcp_connected, daq_connected)

def ipc_callback(ipc, message, remote_address):
    """To be called when an IPC message comes in"""
    global tcp_connected
    global mydaq
    # UDP process wants to know if TCP is connected to PC
    if message == "Open?":
        if tcp_connected:
            reply = "1"
        else:
            reply = "0"
        ipc.send(reply, remote_address)
        if DEBUG_ON:
            print("Callback reply to UDP:", reply)
    # TH Meter GUI wants temperature and humidity readings
    elif message == "SendTH":
        if tcp_connected:
            temperature = mydaq.temperature
            humidity = mydaq.humidity
        else:
            (ok, temperature, humidity) = (True, False, False)
        reply = f"{temperature:.2f},{humidity:.2f}"
        ipc.send(reply, remote_address)
        if DEBUG_ON:
            print("Callback reply to GUI:", reply)

def main():
    """Main program of SC28 firmware"""
    global tcp_connected
    global mydaq
    print("\nMain program starting")
    # Initialise as not connected and no environment spec
    tcp_connected = False
    daq_connected = False
    # Initialise ports
    # ports.ports_init()
    # Initialise patlite
    # mypatlite = 0
    # Start the IPC server and wait for messages in ipc_callback
    ipc_server_udp = ipc.Ipc(ipc.IPC_SERVER)
    ipc_server_udp.wait(ipc_callback)

    # Loop forever
    while True:
        # If already connected to PC and SC11/SI
        if tcp_connected and daq_connected:
            # Perform communications
            (tcp_connected, daq_connected) = comms(mytcp, mydaq)
        # If not connected to PC
        if not tcp_connected:
            # Turn off LED
   
            # Turn off Patlite
         
            # Open TCP socket
            try: 
                mytcp = tcp.Tcp(tcp_timeout=COMMS_TIMEOUT)
            except tcp.TcpError as err:
                if ERROR_ON:
                    print(err.message)
            else:
                # Connected - Turn on LED
                tcp_connected = True
                # ports.led_on()
        # If not connected to SC11/SI
        if not daq_connected:
            # Initialise SC11/SI
            try:
                mydaq = daq.Daq(force_reboot=False)
                #mydaq = daq.Daq(force_reboot=False, mag_decim=50, mic_decim=50)
            except daq.DaqError as err:
                if ERROR_ON:
                    print(err.message)
            else:
                # DAQ Initialised
                daq_connected = True

# Execute main program
main()
