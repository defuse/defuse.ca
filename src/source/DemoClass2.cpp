#include <cstdlib>
#include <cstdio>
#include <cstring>

class Foo {
    public:
        int v1;
        int v2;
        int v3;

        Foo()
        {
            v1 = 1;
            v2 = 2;
            v3 = 3;
        }

        void doSomething()
        {
            printf("Hello!\n");
        }
};

int main(int argc, char **argv)
{
    Foo f;
    f.doSomething();

    printf("sizeof(f) = %d\n", sizeof(f));

    int *vars = (int *)&f;
    printf("v1 =      %d, v2 =      %d, v3 =      %d\n", f.v1, f.v2, f.v3);
    printf("vars[0] = %d, vars[1] = %d, vars[2] = %d\n",
            vars[0], vars[1], vars[2]);

    vars[0] = 1337;
    printf("v1 = %d\n", f.v1);
}
