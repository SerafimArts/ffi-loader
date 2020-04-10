typedef unsigned long DWORD;
typedef DWORD *LPDWORD;
typedef const char *LPCSTR;
typedef char* LPSTR;
typedef enum { false, true } BOOL;

typedef enum _COMPUTER_NAME_FORMAT {
  ComputerNameNetBIOS,
  ComputerNameDnsHostname,
  ComputerNameDnsDomain,
  ComputerNameDnsFullyQualified,
  ComputerNamePhysicalNetBIOS,
  ComputerNamePhysicalDnsHostname,
  ComputerNamePhysicalDnsDomain,
  ComputerNamePhysicalDnsFullyQualified,
  ComputerNameMax
} COMPUTER_NAME_FORMAT;

extern BOOL GetComputerNameExA(
  COMPUTER_NAME_FORMAT NameType,
  LPSTR lpBuffer,
  LPDWORD nSize
);
