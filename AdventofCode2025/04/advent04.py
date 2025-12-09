import csv
tabelle = []

with open("input.csv", "r", encoding="utf-8") as f:
    for zeile in f:
        zeile = zeile.rstrip("\n")      # Zeilenumbruch entfernen
        tabelle.append(list(zeile)) 

anzahl_zeilen=len(tabelle)-1
anzahl_spalten=len(tabelle[0])-1
gesamt=0
for i, zeile in enumerate(tabelle):
    
    for j, zelle in enumerate(zeile):
        count=0
        if (tabelle[i][j]=='@'):
            if(i-1 >=0):
                if (j-1 >=0):
                    if(tabelle[i-1][j-1]=='@'):
                        count=count+1
            if (i-1 >=0):    
                if(tabelle[i-1][j]=='@'):
                    count=count+1
            if (i-1 >=0):
                    if (j+1 <=anzahl_spalten):
                            if(tabelle[i-1][j+1]=='@'):
                                count=count+1
            if(j-1 >=0):
                if(tabelle[i][j-1]=='@'):
                    count=count+1

            if(j+1 <=anzahl_spalten):              
                if(tabelle[i][j+1]=='@'):
                    count=count+1
            if(i+1 <=anzahl_zeilen):                
                if(j-1 >=0):             
                    if(tabelle[i+1][j-1]=='@'):
                        count=count+1
                            
            if(i+1 <=anzahl_zeilen):               
                if(tabelle[i+1][j]=='@'):
                    count=count+1
            if(i+1 <=anzahl_zeilen): 
                if(j+1 <=anzahl_spalten):
                    if(tabelle[i+1][j+1]=='@'):
                                count=count+1

            print('Zeile' + str(i))
            print('Spalte' + str(i))
            print(count)
            if (count < 4):
                gesamt=gesamt+1
        
print(gesamt)