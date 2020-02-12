#!/bin/bash
cd $(dirname "${BASH_SOURCE[0]}")
OD="$(pwd)"

# Pushes application version into the build information.
SPIRAL_VERSION=2.4.4

# Hardcode some values to the core package
LDFLAGS="$LDFLAGS -X github.com/spiral/roadrunner/cmd/rr/cmd.Version=${SPIRAL_VERSION}"
LDFLAGS="$LDFLAGS -X github.com/spiral/roadrunner/cmd/rr/cmd.BuildTime=$(date +%FT%T%z)"
# remove debug (DWARF) info from binary as well as string and symbol tables
LDFLAGS="$LDFLAGS -s"

build(){
	echo Packaging "$1" Build
	bdir=spiral-${SPIRAL_VERSION}-$2-$3
	rm -rf builds/"$bdir" && mkdir -p builds/"$bdir"
	GOOS=$2 GOARCH=$3 ./build.sh

	if [ "$2" == "windows" ]; then
		mv spiral builds/"$bdir"/spiral.exe
	else
		mv spiral builds/"$bdir"
	fi

	cp README.md builds/"$bdir"
	cp CHANGELOG.md builds/"$bdir"
	cp LICENSE builds/"$bdir"
	cd builds

	if [ "$2" == "linux" ]; then
		tar -zcf "$bdir".tar.gz "$bdir"
	else
		zip -r -q "$bdir".zip "$bdir"
	fi

	rm -rf "$bdir"
	cd ..
}

if [ "$1" == "all" ]; then
	rm -rf builds/
	build "Windows" "windows" "amd64"
	build "Mac" "darwin" "amd64"
	build "Linux" "linux" "amd64"
	build "FreeBSD" "freebsd" "amd64"
	exit
fi

go build -ldflags "$LDFLAGS" -o "$OD/spiral" main.go
