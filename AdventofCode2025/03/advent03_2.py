import csv
array = []
with open("test.csv", newline="", encoding="utf-8") as f:
    reader = csv.reader(f)
    for row in reader:
        array.append(row)
#print(array)
sum=0
gesamt=0
for row in array:
        #print(row)
        battery=str(row)
        # gehe in die einzelne Zeile
        stelle=0
        highest=1

    

        #wir wollen 12 zeichen
        for ziffer in battery[:12]:
            stelle=stelle+1
            print('Stelle: ' + str(stelle))
            for i in range(1,13,1):
                print('Loop 1-12: ' +str(i))
                for y in battery[stelle:]:
                    print(y)
                    if (ziffer + y).isdigit():
                        gesamt= str(ziffer) + str(y)
                        print(gesamt)
                    if (int(gesamt) > highest):
                        highest=int(gesamt)
            sum=int(highest)+sum
        #print(sum)
        #loope durch den string, starte an erster stelle 
            #baue 12 zeichen zusammen









