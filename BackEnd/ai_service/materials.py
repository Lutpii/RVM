"""Material slugs for the 2-bin DSME machine.

Only tin (aluminum) and plastic have a bin. Everything the model can
recognise but this machine can't take (glass, paper, metal, wood, bricks)
is 'reject'; anything unrecognised is 'unknown'. Either way the flap stays
closed and the user takes the item back.
"""

ACCEPTED_MATERIALS = ('aluminum', 'plastic')


def normalize_material(raw_name):
    name = (raw_name or '').lower()
    if 'alumin' in name or 'can' in name:
        return 'aluminum'
    if 'plastic' in name:
        return 'plastic'
    if any(word in name for word in ('glass', 'paper', 'metal', 'wood', 'brick')):
        return 'reject'
    return 'unknown'
