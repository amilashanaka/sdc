import socket
import threading
import time

# Global constants
DEBUG_ON = True
ERROR_ON = True
IPC_CLIENT = 0
IPC_SERVER = 1
IPC_IP_ADDRESS = "127.0.0.1" # Local host
IPC_SERVER_PORT = 53557 # Port for IPC server
IPC_BUFFER_SIZE = 80
IPC_TIMEOUT_SECONDS = 0.25

# Define exceptions
class IpcError(Exception):
    pass

class IpcOpenError(IpcError):
    def __init__(self):
        self.message = "Error opening IPC"
        if ERROR_ON:
            print(self.message)
    
class IpcReceiveError(IpcError):
    def __init__(self):
        self.message = "Error receiving IPC"
        if ERROR_ON:
            print(self.message)

class IpcSendError(IpcError):
    def __init__(self):
        self.message = "Error sending IPC"
        if ERROR_ON:
            print(self.message)

class IpcTimeoutError(IpcError):
    def __init__(self):
        self.message = "Timeout on IPC"
        if ERROR_ON:
            print(self.message)

class Ipc:
    """Initialise UDP for Interprocess communication"""
    def __init__(self, process=IPC_CLIENT, buffer_size=IPC_BUFFER_SIZE, server_port=IPC_SERVER_PORT):
        self.process = process
        self.server_address = (IPC_IP_ADDRESS, server_port)
        
        if self.process == IPC_SERVER:
            # Server receives and sends on server port
            local_address = self.server_address
            if DEBUG_ON:
                print(f"Opening IPC Server on address {local_address}")
        else:
            # Client sends and receives on system-assigned port
            local_address = (IPC_IP_ADDRESS, 0)
            if DEBUG_ON:
                print(f"Opening IPC client on address {local_address}")
        self.buffer_size = buffer_size

        # Create IPv4 UDP sockets
        if DEBUG_ON:
            print("Create IPC socket")
        try:
            self.udp_socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        except:
            raise IpcOpenError()
            
        # For server, set option to reuse address before it times out after server disconnect
        if self.process == IPC_SERVER:
            if DEBUG_ON:
                print("Set IPC socket to allow address reuse")
            try:
                self.udp_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            except:
                self.udp_socket.close()
                raise IpcOpenError()
        
        # Bind UDP address and ports to sockets
        # Using 127.0.0.1 binds to localhost, so it looks only internally
        if DEBUG_ON:
            print("Bind IPC socket")
        try:
            self.udp_socket.bind(local_address)
        except:
            self.udp_socket.close()
            raise IpcOpenError()

        # Set client timeout
        if self.process == IPC_CLIENT:
            try:
                self.udp_socket.settimeout(IPC_TIMEOUT_SECONDS)
            except OSError as err:
                self.udp_socket.close()
                raise IpcOpenError()
    
    def receive(self):
        """Receive a message"""
        if DEBUG_ON:
            print("Receiving IPC message...")
        try:
            (udp_buffer, remote_address) = self.udp_socket.recvfrom(self.buffer_size)
        except socket.timeout:
            self.udp_socket.close()
            raise IpcTimeoutError()
        except:
            self.udp_socket.close()
            raise IpcReceiveError()
        # Convert from ASCII to Unicode UTF-8
        message = udp_buffer.decode("utf-8",'ignore')
        if DEBUG_ON:
            print("Recieved IPC message:", message)
        return (message, remote_address)

    def send(self, message, remote_address=None):        
        """Send a message"""
        # Client does not specify remote_address, so it defaults to server_address
        # Server must specify client address
        if remote_address == None:
            remote_address = self.server_address
        if DEBUG_ON:
            print(f"Sending IPC message: '{message}' to {remote_address}")
        try:
            self.udp_socket.sendto(message.encode("utf-8"), remote_address)
        except:
            self.udp_socket.close()
            raise IpcSendError()

    def loop(self):
        """Loop receiving messages for ever"""
        if DEBUG_ON:
            print("Looping to wait for IPC messages")
        while(True):
            (message, remote_address) = self.receive()
            self.callback(self, message, remote_address)

    def wait(self, callback):
        """Start a receive loop, with callback and return"""
        if DEBUG_ON:
            print("Wait for IPC messages...")
        self.callback = callback
        self.wait_thread = threading.Thread(target=self.loop, args=(), daemon=True) # Stops when program exits
        self.wait_thread.start()
        
    def close(self):
        """Close IPC"""
        if DEBUG_ON:
            print("Closing IPC")
        try:
            self.udp_socket.close()
        except:
            pass

def test_callback(ipc, message, remote_address):
    """Test callback function"""
    print("Callback message:", message)
    reply = "howdy"
    ipc.send(reply, remote_address)
    print("Callback reply:", reply)

def test_ipc_server():
    """Test IPC server"""
    ipc_server = Ipc(IPC_SERVER)
    ipc_server.wait(test_callback)
    
    while(True):
        print(".", end="")
        time.sleep(1)
