class ErrorCodeHandler:
    def __init__(self):
        self.error_codes = {
            1: "Unknown error",
            2: "Invalid command",
            3: "Invalid argument",
            4: "Command not supported",
            5: "Error executing command",
            6: "Error sending data",
            7: "Error receiving data",
            8: "Error with IPC",
            9: "Error with TCP",
            10: "Error with UDP",
            11: "Error with serial communication",
            12: "Error with file access",
            13: "Error with configuration",
            14: "Error with host name",
            15: "Error with IP address",
        }

    def get_error_message(self, error_code):
        if error_code in self.error_codes:
            return self.error_codes[error_code]
        else:
            return "Unknown error code"
