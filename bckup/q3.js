function scale_balancing(scale, weights) {
    weights.sort((a, b) => a - b)
    for (w1 of weights) {
        for (w2 of weights) {
            if ((scale[0] + w1) == (scale[1] + w2)) {
                balance_weights = [w1, w2]
                balance_weights.sort((a, b) => a - b)
                return JSON.stringify(balance_weights).replace(/(\[|\])/g, '')
            }
        }
    }
    for (w1 in weights) {
        for (w2 in weights) {
            if (w2 == w1) { continue }
            if ((scale[0] + weights[w1] + weights[w2]) == scale[1]) {
                balance_weights = [weights[w1], weights[w2]]
                balance_weights.sort((a, b) => a - b)
                return JSON.stringify(balance_weights).replace(/(\[|\])/g, '')
            }
        }
        for (w3 in weights) {
            if (w3 == w1) { continue }
            if ((scale[1] + weights[w1] + weights[w3]) == scale[0]) {
                balance_weights = [weights[w1], weights[w3]]
                balance_weights.sort((a, b) => a - b)
                return JSON.stringify(balance_weights).replace(/(\[|\])/g, '')
            }
        }
    }
    return 'Scale Imbalanced'
}

var scale = [5, 9]
var weights = [1, 2, 6, 7]
answer = scale_balancing(scale, weights)
console.log(answer)