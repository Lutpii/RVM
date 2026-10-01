import unittest

from materials import ACCEPTED_MATERIALS, normalize_material


class NormalizeMaterialTest(unittest.TestCase):
    def test_cans_map_to_aluminum(self):
        for raw in ('aluminum', 'aluminium can', 'cans', 'Aluminum'):
            self.assertEqual(normalize_material(raw), 'aluminum', raw)

    def test_plastic(self):
        for raw in ('plastic', 'plastic bottle', 'Plastic'):
            self.assertEqual(normalize_material(raw), 'plastic', raw)

    def test_materials_without_a_bin_are_rejected(self):
        for raw in ('glass', 'glass bottle', 'paper', 'metal', 'wooden', 'bricks'):
            self.assertEqual(normalize_material(raw), 'reject', raw)

    def test_unrecognised_names_are_unknown(self):
        for raw in ('', None, 'banana'):
            self.assertEqual(normalize_material(raw), 'unknown', raw)

    def test_only_tin_and_plastic_are_accepted(self):
        self.assertEqual(ACCEPTED_MATERIALS, ('aluminum', 'plastic'))


if __name__ == '__main__':
    unittest.main()
