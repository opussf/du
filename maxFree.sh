#!/bin/bash
date
gigFree=${$1:=40}

targetFree=$(( 1024 * 1024 * 1024 * $gigFree ))


fillFile="file.minFree"

free=`df . --total | grep total | awk '{printf "%.0f", $4 * 1024}'`
if [ -f $fillFile ]; then 
	fillSize=`ls -l $fillFile | awk '{print $5}'`
else
	touch $fillFile
	fillSize=0
fi

echo "free: $free"
echo "size: $fillSize"
echo "+_+_+_+_+_+_+_+_+"
echo "want: $targetFree"

newFileSize=$(($free - $targetFree + $fillSize ))
diffSize=$(( newFileSize - $fillSize ))

echo "file: $newFileSize"

if [ $newFileSize -gt 0 ]; then
	if [ $newFileSize -gt $fillSize ]; then
		echo "append $diffSize"
		head -c $diffSize /dev/zero >> $fillFile
	fi
	if [ $newFileSize -lt $fillSize ]; then
		echo "truncate"
		truncate --size $newFileSize $fillFile
	fi
else
	echo "Remove file"
	rm $fillFile
fi
echo "================"
