import socket
import errno
import time

# Global constants
DEBUG_COMMS_ON = False
DEBUG_ON = False
BINARY_ON = False
TCP_PORT = 53556 # Port for TCP communication
TCP_BUFFER_SIZE = 2048
TCP_PACKET1_SIZE = 3
TCP_TIMEOUT_SECONDS = 25

class TcpError(Exception):
    def __init__(self, message):
        self.message = message
        print(self.message)

class Tcp:
    """Initialise TCP object and open connection to PC"""
    def __init__(self, tcp_timeout=TCP_TIMEOUT_SECONDS):
        self.tcp_connection = None # Socket for data transfer
        self.timeout = tcp_timeout
        # Open TCP connection
        # 1. Open a TCP server to wait for a connection
        # 2. Accept connection from PC
        # 3. Make connection non-blocking
        # 4. Return true if successful
        # Create IPv4 TCP socket for listening server
        if DEBUG_ON:
            print("Create TCP socket")
        try:
            tcp_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        except OSError as err:
            tcp_socket.close()
            raise TcpError(f"Error opening TCP socket: {err}")
        # Bind TCP server to socket
        # Using SO_REUSEADDR means we don't have to wait 4 minutes for TIME_WAIT state to time out
        # Using 0.0.0.0 binds to ethernet socket and wifi
        try:
            tcp_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            tcp_socket.bind(("0.0.0.0", TCP_PORT))
        except OSError as err:
            tcp_socket.close()
            time.sleep(10)
            raise TcpError(f"Error binding TCP server: {err}")
        # Listen for one TCP connection
        if DEBUG_ON:
            print("Wait for TCP connection...")
        try:
            tcp_socket.listen(1);
        except OSError as err:
            tcp_socket.close()
            raise TcpError(f"Error listening for TCP connection: {err}")
        # Accept TCP connection
        try:
            (self.tcp_connection, remote_address) = tcp_socket.accept()
        except OSError as err:
            tcp_socket.close()
            raise TcpError(f"Error accepting TCP connection from {remote_address[0]}: {err}")
        # Set TCP connection timeout
        """
        try:
            self.tcp_connection.settimeout(tcp_timeout)
        except OSError as err:
            tcp_socket.close()
            self.tcp_connection.close()
            raise TcpError(f"Error setting TCP connection timeout: {err}")
        """
        try:
            self.tcp_connection.setblocking(False)
        except OSError as err:
            tcp_socket.close()
            self.tcp_connection.close()
            raise TcpError(f"Error setting TCP connection to non-blocking mode: {err}")
        # Success
        print("TCP connection accepted from", remote_address[0])
        tcp_socket.close()
        # Wait until tcp_connection is ready to use
        time.sleep(0.5)
        
    def receive_packet(self, num_bytes):
        """Get Packet from TCP connection with timeout"""
        # Using a non-blocking connection, because
        # settimeout doesn't always work. Sometimes it blocks anyway
        time_start = time.time()
        tcp_buffer = b""
        count = 0
        while len(tcp_buffer) < num_bytes:
            count = count + 1
            # Get data from TCP
            try:
                tcp_buffer += self.tcp_connection.recv(num_bytes - len(tcp_buffer))
            except OSError as err:
                # If there is not enough data, wait and try again
                if err.errno == errno.EWOULDBLOCK:
                    time.sleep(0.001)
                else:
                    raise TcpError(f"Error receiving from TCP: {err}")
            # Check timeout
            time_waited = time.time() - time_start
            if time_waited > self.timeout:
                raise TcpError("Timeout receiving from TCP")
        if DEBUG_ON:
            print(f"TCP received packet size {num_bytes} in time {time_waited*1000:.2f} ms. Loop count {count}")
        return tcp_buffer

    def receive_message(self):
        """Receive message from TCP"""
        # Message consists of 2 packets
        # Packet1: 3 bytes: data_type, data_size_hi, data_size_lo
        # Packet2: data_size bytes
        # Returns (type, size, binarydata)
        data_type = "N"
        data_size = 0
        # Get Packet1: data type and size from TCP connection
        tcp_buffer = self.receive_packet(TCP_PACKET1_SIZE)
        data_type = chr(tcp_buffer[0])
        if data_type not in "AB":
            raise TcpError(f"Error in packet1 data from TCP {data_type}")
        # Get data size from bytes 1 and 2
        data_size = int.from_bytes(tcp_buffer[1:3], byteorder='big')
        # Get packet2: data from TCP connection
        tcp_buffer = self.receive_packet(data_size)
        # Return data
        return (data_type, data_size, tcp_buffer)

    def receive_string(self):
        """Receive string from TCP"""
        # Receive packet
        (data_type, data_size, tcp_buffer) = self.receive_message()
        if data_type != "A":
            raise TcpError(f"Error: Data type expected A, received {data_type}")
        # Convert to string
        tcp_string = tcp_buffer.decode("ascii", "backslashreplace")
        if DEBUG_COMMS_ON:
            print("PC:", tcp_string)
        return(data_size, tcp_string)

    def receive_binary(self, numbytes):
        """Receive binary data from TCP"""
        (data_type, data_size, tcp_buffer) = self.receive_message()
        if data_type != "B":
            raise TcpError(f"Error: Data type expected B, received {data_type}")
        if BINARY_ON:
            print("PC Binary:", data_size)
        if data_size != numbytes:
            raise TcpError(f"Error: Bytes expected {numbytes}, received {data_size}")
        return(data_size, tcp_buffer)

    def send_message(self, data_type, tcp_buffer):
        """Send message to TCP"""
        # Message consists of 2 packets
        # Packet1: 3 bytes: data_type, data_size_hi, data_size_lo
        # Packet2: data_size bytes
        start_time = time.time()
        data_size = len(tcp_buffer)
        if data_size == 0:
            print("Attempt to send empty message")
        else:        
            # Send packet1: data type and size to Ethernet
            packet1 = b'' + data_type.encode('ascii') + data_size.to_bytes(2, byteorder = 'big')
            try:
                self.tcp_connection.sendall(packet1);
                #print("Packet1:", packet1)
            except OSError as err:
                raise TcpError(f"Error sending packet1 data to TCP: {err}")
            # Send packet2: data content to Ethernet
            try:
                self.tcp_connection.sendall(tcp_buffer);
                #print("Packet2:", data_size, tcp_buffer)
            except OSError as err:
                raise TcpError(f"Error sending packet2 data to TCP: {err}")
            duration = time.time() - start_time
            if DEBUG_ON:
                print(f"TCP sent 2 packets in time {duration*1000:.2f} ms")

    def send_string(self, tcp_string):
        """Send string to TCP"""
        if DEBUG_COMMS_ON:
            print("PI:", tcp_string)
        # Decode string to ascii and send it
        tcp_buffer = tcp_string.encode("ascii")
        self.send_message("A", tcp_buffer)

    def send_binary(self, tcp_buffer):
        """Send binary data to TCP"""
        if BINARY_ON:
            print("PI Binary:", len(tcp_buffer))
        self.send_message("B", tcp_buffer)

    def close(self):
        """Close TCP connection"""
        try:
            self.tcp_connection.close()
        except OSError:
            pass
        print("TCP connection closed")
