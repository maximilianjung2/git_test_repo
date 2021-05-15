#https://www.practicepython.org/exercise/2014/02/05/02-odd-or-even.html
int_number=int(input('Gib mir eine Nummer!'))
if int_number % 2 == 0 and int_number % 4 ==0:
    print('nummer ist durch 2 und 4 teilbar')
elif int_number % 2==0:
    print('Nummer ist durch 2 teilbar')
elif int_number % 4==0:
    print('nummer ist durch 4 teilbar')
else:
    print('nummer ist weder durch 2 noch durch 4 teilbar')
