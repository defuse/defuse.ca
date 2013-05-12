#include <cstdlib>
#include <cstdio>
#include <cstring>

class Foo {
    public:
        void doSomething()
        {
            printf("Hello!\n");
        }
};

int main(int argc, char **argv)
{
    Foo f;
    f.doSomething();

    printf("sizeof(f) = %d, f = %d\n", sizeof(f), (int)(*(char *)&f));

    *(char*)&f = 42;
    f.doSomething();

    printf("sizeof(f) = %d, f = %d\n", sizeof(f), (int)(*(char *)&f));
}
