/**
 * Grades Screen
 * Displays student grades by semester with GWA summary
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { Layout } from '../../../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Card,
  Badge,
  Header,
  EmptyState,
} from '../../../src/components/ui';
import { gradesApi } from '../../../src/services/api/grades';
import { subjectsApi } from '../../../src/services/api/subjects';

interface Semester {
  id: string;
  name: string;
  year: string;
  isCurrent: boolean;
}

interface Grade {
  id: string;
  code: string;
  name: string;
  units: number;
  grade: number | null;
  remarks: string;
  instructor: string;
}

interface GWAData {
  current: number;
  cumulative: number;
  unitsEarned: number;
  unitsTaken: number;
  standing: string;
}

export default function GradesScreen() {
  const { colors } = useTheme();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [showSemesterPicker, setShowSemesterPicker] = useState(false);
  
  // API data state
  const [semesters, setSemesters] = useState<Semester[]>([]);
  const [selectedSemester, setSelectedSemester] = useState<Semester | null>(null);
  const [grades, setGrades] = useState<Grade[]>([]);
  const [gwa, setGwa] = useState<GWAData>({
    current: 0,
    cumulative: 0,
    unitsEarned: 0,
    unitsTaken: 0,
    standing: 'Regular',
  });

  const fetchGradesData = useCallback(async () => {
    try {
      console.log('📚 Fetching grades data...');
      
      // Fetch grades
      const gradesResponse = await gradesApi.getAll();
      const gradesData = gradesResponse.data || [];
      
      // Fetch subjects for additional info
      const subjectsResponse = await subjectsApi.getAll();
      const subjectsData = subjectsResponse.data || [];
      
      // Create subject lookup map
      const subjectMap = new Map();
      subjectsData.forEach((s: any) => {
        subjectMap.set(s.id, s);
        subjectMap.set(s.code, s);
      });
      
      // Format grades
      const formattedGrades: Grade[] = gradesData.map((g: any) => {
        const subject = subjectMap.get(g.subject_id) || subjectMap.get(g.subject_code) || {};
        
        // Get final grade or calculate from components
        const finalGrade = g.final_grade || g.grade;
        const gradeValue = finalGrade !== null && finalGrade !== undefined ? parseFloat(finalGrade) : null;
        
        // Build instructor name from subject or grade data
        let instructorName = 'TBA';
        if (subject.instructor_first_name && subject.instructor_last_name) {
          instructorName = `${subject.instructor_first_name} ${subject.instructor_last_name}`;
        } else if (g.instructor) {
          instructorName = g.instructor;
        }
        
        return {
          id: g.id?.toString() || Math.random().toString(),
          code: g.subject_code || subject.code || 'N/A',
          name: g.subject_name || subject.name || 'Unknown Subject',
          units: parseInt(g.units) || parseInt(subject.units) || 3,
          grade: gradeValue,
          remarks: g.remarks || (gradeValue !== null && gradeValue <= 3.0 ? 'Passed' : gradeValue !== null ? 'Failed' : 'In Progress'),
          instructor: instructorName,
        };
      });
      
      setGrades(formattedGrades);
      
      // Calculate GWA
      let totalUnits = 0;
      let weightedSum = 0;
      let unitsEarned = 0;
      let unitsTaken = 0;
      
      formattedGrades.forEach((g) => {
        unitsTaken += g.units; // Count all units taken
        if (g.grade !== null && !isNaN(g.grade)) {
          totalUnits += g.units;
          weightedSum += g.grade * g.units;
          if (g.grade <= 3.0) {
            unitsEarned += g.units;
          }
        }
      });
      
      const currentGwa = totalUnits > 0 ? weightedSum / totalUnits : 0;
      const standing = currentGwa > 0 && currentGwa <= 1.5 ? "Dean's Lister" : 
                       currentGwa > 0 && currentGwa <= 1.75 ? "With Honors" : "Regular";
      
      setGwa({
        current: currentGwa,
        cumulative: currentGwa,
        unitsEarned: unitsEarned,
        unitsTaken: unitsTaken,
        standing: standing,
      });
      
      // Set up semesters
      const currentYear = new Date().getFullYear();
      const defaultSemesters: Semester[] = [
        { id: '1', name: 'First Semester', year: `${currentYear}-${currentYear + 1}`, isCurrent: true },
        { id: '2', name: 'Second Semester', year: `${currentYear - 1}-${currentYear}`, isCurrent: false },
      ];
      
      setSemesters(defaultSemesters);
      if (!selectedSemester) {
        setSelectedSemester(defaultSemesters[0]);
      }
      
    } catch (error) {
      console.error('❌ Error fetching grades:', error);
    } finally {
      setLoading(false);
    }
  }, [selectedSemester]);

  useEffect(() => {
    fetchGradesData();
  }, [fetchGradesData]);

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchGradesData();
    setRefreshing(false);
  };

  const getGradeColor = (grade: number | null) => {
    if (grade === null) return colors.textTertiary;
    if (grade <= 1.5) return colors.success;
    if (grade <= 2.0) return colors.info;
    if (grade <= 2.5) return colors.warning;
    return colors.error;
  };

  const getRemarksVariant = (remarks: string): 'success' | 'warning' | 'info' | 'error' => {
    switch (remarks) {
      case 'Passed':
        return 'success';
      case 'Failed':
        return 'error';
      case 'In Progress':
        return 'info';
      default:
        return 'warning';
    }
  };

  if (loading) {
    return (
      <ThemedView style={[styles.container, styles.loadingContainer]}>
        <ActivityIndicator size="large" color={colors.primary} />
        <ThemedText variant="subheadline" color="secondary" style={{ marginTop: 16 }}>
          Loading grades...
        </ThemedText>
      </ThemedView>
    );
  }

  return (
    <ThemedView style={styles.container}>
      <Header title="Grades" large />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={colors.primary}
          />
        }
      >
        {/* Semester Picker */}
        {selectedSemester && (
          <TouchableOpacity
            style={[styles.semesterPicker, { backgroundColor: colors.surface, borderColor: colors.border }]}
            onPress={() => setShowSemesterPicker(!showSemesterPicker)}
          >
            <View>
              <ThemedText variant="headline">{selectedSemester.name}</ThemedText>
              <ThemedText variant="caption1" color="secondary">
                A.Y. {selectedSemester.year}
              </ThemedText>
            </View>
            <Ionicons
              name={showSemesterPicker ? 'chevron-up' : 'chevron-down'}
              size={20}
              color={colors.textSecondary}
            />
          </TouchableOpacity>
        )}

        {showSemesterPicker && (
          <Card variant="elevated" style={styles.semesterDropdown}>
            {semesters.map((sem: Semester) => (
              <TouchableOpacity
                key={sem.id}
                style={[
                  styles.semesterOption,
                  selectedSemester?.id === sem.id && {
                    backgroundColor: colors.primaryMuted,
                  },
                ]}
                onPress={() => {
                  setSelectedSemester(sem);
                  setShowSemesterPicker(false);
                }}
              >
                <ThemedText
                  variant="body"
                  color={selectedSemester?.id === sem.id ? 'accent' : 'primary'}
                >
                  {sem.name} - {sem.year}
                </ThemedText>
                {sem.isCurrent && (
                  <Badge label="Current" variant="primary" size="sm" />
                )}
              </TouchableOpacity>
            ))}
          </Card>
        )}

        {/* GWA Summary Card */}
        <Card variant="elevated" style={styles.gwaCard}>
          <View style={styles.gwaHeader}>
            <View>
              <ThemedText variant="caption1" color="secondary">
                General Weighted Average
              </ThemedText>
              <ThemedText variant="largeTitle" color="accent">
                {gwa.current > 0 ? gwa.current.toFixed(2) : '--'}
              </ThemedText>
            </View>
            {gwa.current > 0 && (
              <Badge label={gwa.standing} variant="success" />
            )}
          </View>

          <View style={styles.gwaDivider} />

          <View style={styles.gwaStats}>
            <View style={styles.gwaStat}>
              <ThemedText variant="headline">
                {gwa.cumulative > 0 ? gwa.cumulative.toFixed(2) : '--'}
              </ThemedText>
              <ThemedText variant="caption2" color="secondary">
                Cumulative GWA
              </ThemedText>
            </View>
            <View style={[styles.gwaStatDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.gwaStat}>
              <ThemedText variant="headline">{gwa.unitsEarned}</ThemedText>
              <ThemedText variant="caption2" color="secondary">
                Units Earned
              </ThemedText>
            </View>
            <View style={[styles.gwaStatDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.gwaStat}>
              <ThemedText variant="headline">{gwa.unitsTaken}</ThemedText>
              <ThemedText variant="caption2" color="secondary">
                Units Taken
              </ThemedText>
            </View>
          </View>
        </Card>

        {/* Grades List */}
        <View style={styles.gradesSection}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Subjects ({grades.length})
          </ThemedText>

          {grades.length === 0 ? (
            <EmptyState
              icon="document-text-outline"
              title="No Grades Yet"
              message="Your grades will appear here once they are released."
            />
          ) : (
            grades.map((grade: Grade) => (
              <Card
                key={grade.id}
                variant="elevated"
                style={styles.gradeCard}
                onPress={() => router.push(`/(app)/grades/${grade.id}`)}
              >
                <View style={styles.gradeHeader}>
                  <View style={styles.gradeInfo}>
                    <ThemedText variant="headline">{grade.name}</ThemedText>
                    <ThemedText variant="caption1" color="secondary">
                      {grade.code} • {grade.units} units
                    </ThemedText>
                  </View>
                  <View style={styles.gradeValue}>
                    <ThemedText
                      variant="title2"
                      style={{ color: getGradeColor(grade.grade) }}
                    >
                      {grade.grade !== null ? grade.grade.toFixed(2) : '--'}
                    </ThemedText>
                    <Badge
                      label={grade.remarks}
                      variant={getRemarksVariant(grade.remarks)}
                      size="sm"
                    />
                  </View>
                </View>
                <View style={styles.gradeFooter}>
                  <Ionicons
                    name="person-outline"
                    size={14}
                    color={colors.textTertiary}
                  />
                  <ThemedText variant="caption1" color="tertiary">
                    {grade.instructor}
                  </ThemedText>
                </View>
              </Card>
            ))
          )}
        </View>

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
  },
  semesterPicker: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Layout.spacing.md,
    borderRadius: Layout.borderRadius.md,
    borderWidth: 1,
    marginBottom: Layout.spacing.md,
  },
  semesterDropdown: {
    marginBottom: Layout.spacing.md,
    overflow: 'hidden',
  },
  semesterOption: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  gwaCard: {
    marginBottom: Layout.spacing.lg,
  },
  gwaHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    padding: Layout.spacing.md,
  },
  gwaDivider: {
    height: StyleSheet.hairlineWidth,
    backgroundColor: '#E0E0E0',
  },
  gwaStats: {
    flexDirection: 'row',
    padding: Layout.spacing.md,
  },
  gwaStat: {
    flex: 1,
    alignItems: 'center',
  },
  gwaStatDivider: {
    width: 1,
    height: '100%',
  },
  gradesSection: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
  },
  gradeCard: {
    marginBottom: Layout.spacing.sm,
  },
  gradeHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: Layout.spacing.md,
  },
  gradeInfo: {
    flex: 1,
    paddingRight: Layout.spacing.md,
  },
  gradeValue: {
    alignItems: 'flex-end',
  },
  gradeFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: Layout.spacing.md,
    paddingBottom: Layout.spacing.md,
  },
});
