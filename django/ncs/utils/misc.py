def getChoiceValue(choiceTuple, key):
    for (k,v) in choiceTuple:
        if k == key: 
            return v
    return None