#define DEFAULT_USER		"apache"

#include <stdio.h>
#include <unistd.h>
#include <pwd.h>
#include <errno.h>
#include <malloc.h>
#include <string.h>

char *get_user_home_dir(char *user)
{
	struct passwd *pwd;

	pwd = getpwnam(user);
	if (!pwd)
		return NULL;

	return pwd->pw_dir;
}

int get_user_uid(char *user)
{
	struct passwd *pwd;

	pwd = getpwnam(user);
	if (!pwd)
		return -1;

	return pwd->pw_uid;
}

int file_exists(char *file)
{
	return (access(file, F_OK) == 0);
}

int generate_keyfile(char *keyfile)
{
	FILE *fp;
	char *keyfn = NULL;
	char cmd[2048] = { 0 };

	if (file_exists(keyfile))
		return -EEXIST;

	keyfn = strdup(keyfile);
	if (memcmp(keyfn + (strlen(keyfn) - 4), ".pub", 4) == 0)
		keyfn[ strlen(keyfn) - 4 ] = 0;

	snprintf(cmd, sizeof(cmd), "ssh-keygen -t rsa -f %s", keyfn);
	fp = popen(cmd, "w");
	fputc(13, fp);
	fclose(fp);

	free(keyfn);

	return file_exists(keyfile);
}

void copy_key(char *keyfile, char *host)
{
	char cmd[2048] = { 0 };

	snprintf(cmd, sizeof(cmd), "ssh-copy-id -i %s root@%s", keyfile, host);

	system(cmd);
}

int main(int argc, char *argv[])
{
	int uid, olduid;
	char *dir = NULL;
	char *user = DEFAULT_USER;
	char keyfile[1024] = { 0 };

	if (getuid() != 0) {
		fprintf(stderr, "Error: You must run this utility as root!\n");
		return 1;
	}

	if (argc != 2) {
		fprintf(stderr, "Syntax: %s remote-machine\n", argv[0]);
		return 2;
	}

	putenv("DISPLAY=");
	dir = get_user_home_dir(user);
	if (!dir) {
		fprintf(stderr, "Error: Cannot locate directory for user %s\n", user);
		return 3;
	}

	uid = get_user_uid(user);
	if (uid < 0) {
		fprintf(stderr, "Error: Cannot get uid for user %d\n", user);
		return 4;
	}
	olduid = getuid();
	setuid(uid);

	snprintf(keyfile, sizeof(keyfile), "%s/.ssh/id_rsa.pub", dir);
	if (!file_exists(keyfile)) {
		if (generate_keyfile(keyfile) != 1) {
			fprintf(stderr, "Error: Cannot create SSH-RSA key for user %s\n", user);
			setuid(olduid);
			return 5;
		}
	}

	copy_key(keyfile, argv[1]);
	setuid(olduid);

	printf("Key has been copied to %s\n", argv[1]);

	return 0;
}

