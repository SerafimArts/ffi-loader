#define FFI_LIB kernel32.dll

typedef const char* LPCSTR;
typedef enum { false, true } BOOL;

extern BOOL SetDllDirectoryA(LPCSTR lpPathName);
