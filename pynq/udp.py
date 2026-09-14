# udp.py - Python3 version - Spicer Consulting
import socket
import time
import ipc
import subprocess
import sys


# Global constants
DEBUG_ON = False
UDP_PORT = 53555 # Port for UDP handshake
UDP_BUFFER_SIZE = 30
UDP_PACKET_IN = "ScHelloDevice" # ID string for incomming UDP handshake. Max UDP_BUFFER_SIZE
DEVICE_NAME = "SC28/SI"
STARTUP = '/home/pi/sc/startup'

# Exception for UDP
class UdpError(Exception):
    def __init__(self, message):
        self.message = message
        print(self.message)

class Udp:
    def __init__(self):
        """Initialise object and open UDP socket"""
        # Initialise object variables
        self.udp_socket = None
        # 1. Start a UDP server
        # Create IPv4 UDP socket
        if DEBUG_ON:
            print("Create UDP socket")
        try:
            self.udp_socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        except OSError:
            self.udp_socket.close()
            raise UdpError("Error opening UDP socket")
        # Bind UDP server to socket
        # Using 0.0.0.0 binds to ethernet socket and wifi
        try:
            self.udp_socket.bind(("0.0.0.0", UDP_PORT))
        except OSError:
            self.udp_socket.close()
            raise UdpError("Error binding UDP server")
    
    def handshake(self, ipc_client):
        """UDP Handshake - establish communication with PC"""
        # 2. Wait for a packet from PC 
        # 3. Reply, giving IP address
        # Wait for UDP packet
        if DEBUG_ON:
            print("Wait for UDP packet...")
        try:
            (udp_buffer, remote_address) = self.udp_socket.recvfrom(UDP_BUFFER_SIZE)
        except OSError:
            self.udp_socket.close()
            raise UdpError("Error receiving UDP data")
        
        # Convert from ASCII to Unicode UTF-8
        udp_in = udp_buffer.decode("utf-8")    
        if DEBUG_ON:
            print("Received UDP Packet from", remote_address[0], ":", udp_in)
            
        # If the recieved UDP packet matches the expected string
        if udp_in == UDP_PACKET_IN:
            # Get serial number from config file
            serial_number = "12345"
            # Check if device is connected to TCP
            connected = udp_check_tcp(ipc_client)
            # If Main process has crashed, attempt to restart both processes
            if connected == "None":
                udp_restart()
            # Send the reply string in ASCII
            reply = DEVICE_NAME + ",20" + serial_number + "," + "2.21"+ "," + connected
            try:
                self.udp_socket.sendto(reply.encode("utf-8"), (remote_address[0], UDP_PORT))
            except OSError:
                self.udp_socket.close()
                raise UdpError("Error sending UDP packet")
            # Success
            if DEBUG_ON:
                print("Sent UDP Packet to", remote_address[0], ":", reply)
                
    def close(self):
        """Close UDP connection"""
        try:
            self.udp_socket.close()
        except OSError:
            pass
        if DEBUG_ON:
            print("UDP connection closed")

def udp_check_tcp(ipc_client):
    """Check if TCP connection is open (by IPC message to main process)"""
    connected = "None"
    if DEBUG_ON:
        print("IPC Message: Open?")
    try:
        ipc_client.send("Open?")
    except ipc.IpcSendError:
        pass
    else:
        try:
            (connected, remote_address) = ipc_client.receive()
        except ipc.IpcTimeoutError:
            pass
    if DEBUG_ON:
        print(f"IPC Reply: '{connected}'")
    if connected == "None":
        print("Error: SC28 main process has crashed")
    return connected

def udp_restart():
    """Restart both SC28 programs"""
    print("Restarting firmware...")
    try:
        p = subprocess.Popen(['/bin/sh', '-c', STARTUP])
    except OSError:
        raise UdpError("Error restarting firmware")
    # Quit this process and never return
    sys.exit(0)

def udp_main():
    """Main program of SC28 UDP process"""
    # Sleep to allow main program to initialise
    time.sleep(2)
    print("UDP process starting")
    # Open IPC client
    ipc_client = ipc.Ipc(ipc.IPC_CLIENT)

    # Loop for ever
    while True:
        # Initialise UDP socket. If failed, wait and try again.
        while True:
            try:
                myudp = Udp()
            except UdpError:
                time.sleep(10)
            else:
                break
                
        # Loop performing handshakes unless error
        while True:
            try:
                myudp.handshake(ipc_client)
            except UdpError:
                myudp.close()
                break
            else:
                time.sleep(0.01)

        # Wait before trying again
        time.sleep(5)

# If this is the main prgram
if __name__ == "__main__":
    udp_main()
